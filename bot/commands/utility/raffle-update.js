const {
    SlashCommandBuilder,
    PermissionFlagsBits,
    ActionRowBuilder, StringSelectMenuBuilder
} = require('discord.js');
const axios = require("axios");

module.exports = {
    data: new SlashCommandBuilder()
        .setName('raffle-update')
        .setDescription('Вызывает редактирование розыгрыша')
        .setDefaultMemberPermissions(PermissionFlagsBits.BanMembers),
    async execute(interaction) {
        await axios.get('/raffles/not-completed-list')
            .then(res => {
                if(res.data.list.length === 0) {
                    return interaction.update({
                        content: 'Не найдено не завершенных розыгрышей.',
                        ephemeral: true
                    });
                }
                const select = new StringSelectMenuBuilder()
                    .setCustomId('raffleAfterUpdateSelector')
                    .setPlaceholder('Выберите розыгрыш')
                    .addOptions(res.data.list.map(raffle => {
                        return {
                            label: raffle.title,
                            value: raffle.id.toString()
                        }
                    }));

                const row = new ActionRowBuilder()
                    .addComponents(select);

                return interaction.reply({
                    content: 'Выберите розыгрыш из списка',
                    components: [row],
                    ephemeral: true
                });
            })
    }
};
