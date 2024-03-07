const { SlashCommandBuilder } = require('discord.js');
const axios = require('axios');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('balance')
        .setDescription('Выведет ваш баланс на сервере'),
    async execute(interaction) {
        const {user} = interaction;
        await axios.post(`/users/balance`, {
                user: {
                    discord_id: user.id,
                    name: user.globalName,
                    avatar_url: `https://cdn.discordapp.com/avatars/${user.id}/${user.avatar}.png`
                }
            })
            .then(res => {
                interaction.reply({content: `Ваш баланс:\nВ-Баксы: **${res.data.vbucks}**\nРубли: **${res.data.fiat}**`, ephemeral: true});
            })
            .catch(err => {
                console.log(err.response.data.message)
                interaction.reply({content: `Произошла ошибка`, ephemeral: true});
            });
    },
};
