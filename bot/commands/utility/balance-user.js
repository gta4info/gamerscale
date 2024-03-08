const { SlashCommandBuilder, PermissionFlagsBits} = require('discord.js');
const axios = require('axios');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('balance-user')
        .setDescription('Выведет баланс выбранного пользователя на сервере')
        .setDefaultMemberPermissions(PermissionFlagsBits.BanMembers)
        .addUserOption(option =>
            option
                .setName('user')
                .setDescription('Выберите пользователя')
                .setRequired(true)
        ),
    async execute(interaction) {
        const target = interaction.options.getUser('user');
        await axios.post(`/users/balance`, {
                user: {
                    discord_id: target.id,
                    name: target.globalName,
                    avatar_url: `https://cdn.discordapp.com/avatars/${target.id}/${target.avatar}.png`
                },
            })
            .then(res => {
                interaction.reply({content: `Баланс <@${target.id}>:\nВ-Баксы: **${res.data.vbucks}**\nРубли: **${res.data.fiat}**`, ephemeral: true});
            })
            .catch(err => {
                console.log(err.response.data.message)
                interaction.reply({content: `Произошла ошибка`, ephemeral: true});
            });
    },
};
