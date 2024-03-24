const fs = require('node:fs');
const path = require('node:path');

// Express
const express = require('express')
const app = express()
const bodyParser = require('body-parser');
const port = 1337
app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());
app.use(express.urlencoded({ extended: false }));
app.use(express.json());

// Require the necessary discord.js classes
const { Client, Partials, Collection, Events, GatewayIntentBits, ActionRowBuilder, StringSelectMenuBuilder, EmbedBuilder, ButtonBuilder, ButtonStyle, ModalBuilder, TextInputBuilder, TextInputStyle} = require('discord.js');
const { token, env, url_local, url_prod, raffles_channel_id } = require('./config.json');
const axios = require('axios');
const moment = require('moment/moment')

axios.defaults.baseURL = (env === 'prod' ? url_prod : url_local) + '/api';

// Create a new client instance
global.client = new Client({ intents: [GatewayIntentBits.Guilds, GatewayIntentBits.GuildMessages], partials: [Partials.Message, Partials.Channel, Partials.Reaction] });

// When the client is ready, run this code (only once).
// The distinction between `client: Client<boolean>` and `readyClient: Client<true>` is important for TypeScript developers.
// It makes some properties non-nullable.
client.once(Events.ClientReady, readyClient => {
    console.log(`Discord Bot started with name ${readyClient.user.tag}`);
});

// Log in to Discord with your client's token
client.login(token);

client.commands = new Collection();

const foldersPath = path.join(__dirname, 'commands');
const commandFolders = fs.readdirSync(foldersPath);

for (const folder of commandFolders) {
    const commandsPath = path.join(foldersPath, folder);
    const commandFiles = fs.readdirSync(commandsPath).filter(file => file.endsWith('.js'));
    for (const file of commandFiles) {
        const filePath = path.join(commandsPath, file);
        const command = require(filePath);
        // Set a new item in the Collection with the key as the command name and the value as the exported module
        if ('data' in command && 'execute' in command) {
            client.commands.set(command.data.name, command);
        } else {
            console.log(`[WARNING] The command at ${filePath} is missing a required "data" or "execute" property.`);
        }
    }
}

let tempRaffleId = [];

client.on(Events.MessageDelete, async message => {
    if(message.channelId === raffles_channel_id) {
        try {
            await axios.delete(`/raffles/delete-by-message-id/${message.id}`);
        } catch (err) {}
    }
});

client.on(Events.InteractionCreate, async interaction => {
    // On create raffle button pressed (Step 1)
    if(interaction.isButton() && interaction.customId === 'createRaffleBtn') {
        await axios.get('/raffles/not-published-list')
            .then(res => {
                if(res.data.list.length === 0) {
                    return interaction.update({
                        content: 'Не найдено не опубликованных розыгрышей. Убедитесь что вы создали розыгрыш на сайте и попробуйте использовать команду еще раз!',
                        ephemeral: true
                    });
                }
                const select = new StringSelectMenuBuilder()
                    .setCustomId('raffleAfterCreatedSelector')
                    .setPlaceholder('Выберите розыгрыш')
                    .addOptions(res.data.list.map(raffle => {
                        return {
                            label: raffle.title,
                            value: raffle.id.toString()
                        }
                    }));

                const row = new ActionRowBuilder()
                    .addComponents(select);

                return interaction.update({
                    content: 'Выберите розыгрыш из списка',
                    components: [row],
                    ephemeral: true
                });
            })
        return;
    }

    // After selection from dropdown (Step 2)
    if(interaction.customId === 'raffleAfterCreatedSelector') {
        tempRaffleId[interaction.user.id] = interaction.values[0];
        await axios.get(`/raffles/get/${tempRaffleId[interaction.user.id]}`)
            .then(res => {
                const publishBtn = new ButtonBuilder()
                    .setCustomId('publishRaffleBtn')
                    .setLabel('Опубликовать')
                    .setStyle(ButtonStyle.Primary);

                const changeBtn = new ButtonBuilder()
                    .setLabel('Изменить')
                    .setURL((env === 'prod' ? url_prod : url_local) +'/raffle-update-view/' + tempRaffleId[interaction.user.id])
                    .setStyle(ButtonStyle.Link);

                const deleteBtn = new ButtonBuilder()
                    .setCustomId('deleteRaffleBtn')
                    .setLabel('Удалить')
                    .setStyle(ButtonStyle.Danger);

                const row = new ActionRowBuilder()
                    .addComponents(publishBtn, changeBtn, deleteBtn);

                interaction.update({content: 'Предпросмотр сообщения с розыгрышем:', embeds: [raffleEmbedContent(res.data)], components: [row], ephemeral: true});
            })
        return;
    }

    // Publish the raffle (Step 2 choice)
    if(interaction.customId === 'publishRaffleBtn') {
        await axios.post(`/raffles/publish/${tempRaffleId[interaction.user.id]}`)
            .then(res => {
                const { raffle } = res.data;
                client.channels.fetch(raffles_channel_id)
                    .then(channel => {
                        channel
                            .send({content: raffle.discord_message_content, embeds: [raffleEmbedContent(raffle)]})
                            .then(msg => {
                                axios.post(`/raffles/set-discord-message-id/${tempRaffleId[interaction.user.id]}`, {
                                    discord_message_id: msg.id
                                })
                            });
                    });

                interaction
                    .update({content: `Розыгрыш успешно опубликован в канал <#${raffles_channel_id}>.`, embeds: [], components: [], ephemeral: true})
                    .then(msg => setTimeout(() => msg.delete(), 5000));
            })
            .catch(err => {
                interaction.update({content: 'Ошибка при публикации розыгрыша.', ephemeral: true});
            })
            .finally(() => removeTempRaffleId(interaction.user.id))
        return;
    }

    // Delete the raffle (Step 2 choice)
    if(interaction.customId === 'deleteRaffleBtn') {
        axios.delete(`/raffles/delete/${tempRaffleId[interaction.user.id]}`)
            .then(res => {
                interaction.update({content: 'Розыгрыш удален.', embeds: [], components: [], ephemeral: true}).then(msg => setTimeout(() => msg.delete(), 5000));
            })
            .catch(err => {
                interaction.update({content: 'Ошибка при удалении розыгрыша.', ephemeral: true});
            })
            .finally(() => removeTempRaffleId(interaction.user.id))
        return;
    }

    // After selection raffle to update (Step 1)
    if(interaction.customId === 'raffleAfterUpdateSelector') {
        tempRaffleId[interaction.user.id] = interaction.values[0];

        const urlUpdateBtn = new ButtonBuilder()
            .setLabel('Обновить розыгрыш')
            .setURL((env === 'prod' ? url_prod : url_local) +'/raffle-update-view/' + tempRaffleId[interaction.user.id])
            .setStyle(ButtonStyle.Link);

        const row = new ActionRowBuilder()
            .addComponents(urlUpdateBtn);

        await interaction.update({components: [row], ephemeral: true})
            .then(() => removeTempRaffleId(interaction.user.id))
        return;
    }

    // User clicked button to start participation (Step 1)
    if(interaction.customId === 'participateRaffleBtn') {
        axios.get(`/raffles/get-participation-info/${interaction.message.id}`)
            .then(res => {
                const {id,type} = res.data;

                if(type === 0) {
                    (async () => {
                        const message = await participate(id, interaction.user)

                        await interaction.reply({content: message, ephemeral: true})
                    })()
                } else {
                    const modal = new ModalBuilder()
                        .setCustomId('participationTicketsAmountRaffleModal')
                        .setTitle('Участие в розыгрыше');
                    const ticketsAmountInput = new TextInputBuilder()
                        .setCustomId('tickets_amount')
                        .setLabel("Введите кол-во билетов:")
                        .setStyle(TextInputStyle.Short)
                        .setRequired(true)
                        .setValue('1');
                    const ticketsAmountRow = new ActionRowBuilder().addComponents(ticketsAmountInput);

                    modal.addComponents(ticketsAmountRow);

                    interaction.showModal(modal);
                }
            }).catch(err => {
                console.error(err)
            })

        return;
    }

    if(interaction.customId === 'participationTicketsAmountRaffleModal') {
        const tickets_amount = parseInt(interaction.fields.getTextInputValue('tickets_amount'));

        if(isNaN(tickets_amount) || tickets_amount < 1) {
            await interaction.reply({content: 'Поле заполнено не верно, повторите попытку и будьте внимательны!', ephemeral: true})

            return;
        }

        const raffleId = await axios.get(`/raffles/get-participation-info/${interaction.message.id}`)
            .then(res => res.data.id)

        await (async () => {
            const message = await participate(raffleId, interaction.user, tickets_amount)

            await interaction.reply({content: message, ephemeral: true})
        })()

        return;
    }

    const command = interaction.client.commands.get(interaction.commandName);

    if (!command) {
        console.error(`No command matching ${interaction.commandName} was found.`);
        return;
    }

    try {
        await command.execute(interaction);
    } catch (error) {
        console.error(error);
        if (interaction.replied || interaction.deferred) {
            await interaction.followUp({ content: 'There was an error while executing this command!', ephemeral: true });
        } else {
            await interaction.reply({ content: 'There was an error while executing this command!', ephemeral: true });
        }
    }
});

function removeTempRaffleId(val) {
    const index = tempRaffleId.indexOf(val);
    if (index > -1) { // only splice array when item is found
        tempRaffleId.splice(index, 1); // 2nd parameter means remove one item only
    }
}

let raffleEmbedContent = (raffle) => {
    const currencyType = resolveCurrencyType(raffle.currency_type)
    let fields = [
        {
            name: '\n',
            value: '\n',
            inline: false,
        },
        {
            name: 'Тип розыгрыша',
            value: currencyType.type,
            inline: true,
        },
        {
            name: '\n',
            value: '\n',
            inline: false,
        },
        {
            name: 'Кол-во призов',
            value: raffle.winners_amount.toString(),
            inline: true,
        },
        {
            name: '\n',
            value: '\n',
            inline: false,
        },
    ];

    if(raffle.currency_type !== 0) {
        fields.splice(2, 0, {
            name: 'Стоимость билета',
            value: raffle.cost.toString(),
            inline: true,
        });
    }

    if(raffle.status !== 0) {
        let index = raffle.currency_type === 0 ? 4 : 5;
        fields.splice(index, 0, {
            name: 'Кол-во участников',
            value: raffle.participants_amount.toString(),
            inline: true,
        });
    }

    if(raffle.hasOwnProperty('winners')) {
        fields.push({
            name: 'Победители',
            value: winnersTags(raffle.winners).join(', '),
            inline: false,
        })
    }

    return {
        color: currencyType.color,
        title: raffle.title,
        author: {
            name: 'Розыгрыш от GamerScale',
        },
        description: raffle.description,
        fields: fields,
        footer: {
            text: `Начало в: ${moment(raffle.start_at).add(3, 'hours').format('YYYY-MM-DD HH:mm')} МСК\nИтоги в: ${moment(raffle.end_at).add(3, 'hours').format('YYYY-MM-DD HH:mm')} МСК`,
        },
    };
}

app.listen(port, () => {
    console.log(`Discord Bot server listening port ${port}`)
})

app.post('/update-message', (req, res) => {
    const raffle = req.body;

    try {
        client.channels.cache.get(raffles_channel_id).messages.fetch(raffle.discord_message_id).then((msg) => {
            let msgObj = {
                content: raffle.discord_message_content,
                embeds: [raffleEmbedContent(raffle)],
                components: []
            };

            if(raffle.hasOwnProperty('winners')) {
                msg.reply({content: `Розыгрыш завершен.\nПоздравляем победителей: ${winnersTags(raffle.winners).join(', ')}`})
            }

            // When raffle is going active
            if(raffle.status === 1) {
                const participateBtn = new ButtonBuilder()
                    .setCustomId('participateRaffleBtn')
                    .setLabel('Участвовать')
                    .setStyle(ButtonStyle.Primary);

                const row = new ActionRowBuilder()
                    .addComponents(participateBtn);

                msgObj.components = [row];
            }

            msg.edit(msgObj);
        });
    } catch (err) {
        console.log(err)
        res.status(400)
        res.send(err)
    }

    res.send('ok');
})

function resolveCurrencyType(val) {
    let type = '';
    let color = '';
    switch (parseInt(val)) {
        case 0:
            type = 'Бесплатный';
            color = 3447003;
            break;
        case 1:
            type = 'V-Bucks';
            color = 10181046;
            break;
        case 2:
            type = 'Рубли';
            color = 15844367;
            break;
    }

    return {
        type: type,
        color: color
    }
}

async function participate(raffleId, user, amount = 1) {
    let message = '';
    await axios.post(`/raffles/participate/${raffleId}`, {
        user: collectUserObject(user),
        tickets_amount: amount
    }).then(res => {
        const {purchased_amount,total_amount,winning_chance} = res.data;
        message = `Приобретено билетов - **${purchased_amount}**. Ваших билетов в розыгрыше - **${total_amount}**. Ваш шанс на победу в текущий момент составляет **${winning_chance}%**!\nЧтобы проверить актуальную информацию используйте команду **/raffle-me**`;
    }).catch(err => {
        message = err.response.data.message;
    })

    return message;
}

function collectUserObject(data) {
    return {
        discord_id: data.id,
        name: data.globalName,
        avatar_url: `https://cdn.discordapp.com/avatars/${data.id}/${data.avatar}.png`
    }
}

function winnersTags(array) {
    let tags = [];
    array.forEach((winner, index) => {
        tags.push(`<@${winner}>`);
    });

    return tags;
}
