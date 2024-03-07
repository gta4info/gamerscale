const {
    SlashCommandBuilder,
    PermissionFlagsBits,
} = require('discord.js');
const axios = require("axios");

module.exports = {
    data: new SlashCommandBuilder()
        .setName('balance-control')
        .setDescription('Позволяет изменить баланс пользователя в ручном режиме.')
        .setDefaultMemberPermissions(PermissionFlagsBits.BanMembers)
        .addUserOption(option =>
            option
                .setName('user')
                .setDescription('Выберите пользователя')
                .setRequired(true)
        )
        .addStringOption(option =>
            option
                .setName('currency')
                .setDescription('Выберите валюту')
                .addChoices(
                    {name: 'Рубли', value: '0'},
                    {name: 'В-Баксы', value: '1'}
                )
                .setRequired(true)
        )
        .addStringOption(option =>
            option
                .setName('action')
                .setDescription('Тип операции')
                .addChoices(
                    {name: 'Добавить', value: 'give'},
                    {name: 'Отнять', value: 'take'}
                )
                .setRequired(true)
        )
        .addNumberOption(option =>
            option
                .setName('amount')
                .setDescription('Введите сумму')
                .setMinValue(1)
                .setRequired(true)
        ),
    async execute(interaction) {
        const target = interaction.options.getUser('user');
        const currency = interaction.options.getString('currency');
        const action = interaction.options.getString('action');
        const amount = interaction.options.getNumber('amount');

        await axios.post('/users/balance/create', {
            user: {
                discord_id: target.id,
                name: target.globalName,
                avatar_url: `https://cdn.discordapp.com/avatars/${target.id}/${target.avatar}.png`
            },
            currency_type: currency,
            action: action,
            amount: amount
        }).then(res => {
            interaction.reply({content: res.data.message, ephemeral: true});
        }).catch(err => {
            interaction.reply({content: err.response.data.message, ephemeral: true});
        })
    }
};
