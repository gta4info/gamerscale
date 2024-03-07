const { SlashCommandBuilder } = require('discord.js');
const axios = require('axios');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('raffle-me')
        .setDescription('Покажет вам в каких розыгрышах вы участвуете, сколько у вас билетов и шанс выигрыша!'),
    async execute(interaction) {
        const {user} = interaction;
        await axios.get('/raffles/user-summaries-list', {
            data: {
                user: {
                    discord_id: user.id,
                    name: user.globalName,
                    avatar_url: `https://cdn.discordapp.com/avatars/${user.id}/${user.avatar}.png`
                }
            }
        })
            .then(res => {
                let replyContent = '';

                if(res.data.length === 0) {
                    replyContent = 'Вы не принимаете участие в активных розыгрышах!'
                }

                for(let i = 0; i < res.data.length; i++) {
                    if(res.data[i].tickets > 0) {
                        replyContent += `В розыгрыше: **«${res.data[i].title}»** у вас приобретено **${res.data[i].tickets}** билетов. Текущий шанс на победу **${res.data[i].chance}%**!`
                    } else {
                        replyContent += `Вы еще не принимаете участие в розыгрыше: **«${res.data[i].title}»**!`
                    }

                    if(i < res.data.length - 1) {
                        replyContent += '\n ------------------------ \n';
                    }
                }

                interaction.reply({content: replyContent, ephemeral: true});
            })
            .catch(err => {
                console.log(err.response.data.message)
                interaction.reply({content: `Произошла ошибка`, ephemeral: true});
            });
    },
};
