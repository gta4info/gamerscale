const {
    SlashCommandBuilder,
    PermissionFlagsBits,
    ButtonBuilder,
    ButtonStyle,
    ActionRowBuilder
} = require('discord.js');
const { env, url_local, url_prod } = require('../../config.json');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('raffle-create')
        .setDescription('Вызывает кнопку создания розыгрыша')
        .setDefaultMemberPermissions(PermissionFlagsBits.BanMembers),
    async execute(interaction) {
        const urlBtn = new ButtonBuilder()
                    .setLabel('Создать розыгрыш')
                    .setURL((env === 'prod' ? url_prod : url_local) +'/raffle-create-view')
                    .setStyle(ButtonStyle.Link);
        const confirmBtn = new ButtonBuilder()
                    .setCustomId('createRaffleBtn')
                    .setLabel('Я создал розыгрыш')
                    .setStyle(ButtonStyle.Primary);

        const row = new ActionRowBuilder()
            .addComponents(urlBtn, confirmBtn);

        await interaction.reply({components: [row], ephemeral: true})
    }
};
