const { ContextMenuCommandBuilder, PermissionFlagsBits, ApplicationCommandType} = require('discord.js');
const axios = require('axios');

module.exports = {
    data: new ContextMenuCommandBuilder()
        .setName('Reroll')
        .setType(ApplicationCommandType.Message)
        .setDefaultMemberPermissions(PermissionFlagsBits.BanMembers),
    async execute(interaction) {
        await axios.post(`/raffles/reroll/${interaction.targetId}`)
            .then(res => {
                interaction.reply({content: res.data.message, ephemeral: true});
            })
            .catch(err => {
                console.log(err.response.data.message)
                interaction.reply({content: err.response.data.message, ephemeral: true});
            });
    },
};
