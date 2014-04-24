YUI.add('moodle-theme_decaf-actionmenu', function(Y) {

/**
 * Action menu enhancements for Decaf.
 */
var ActionMenu = function() {
    ActionMenu.superclass.constructor.apply(this, arguments);
};
ActionMenu.prototype = {
    initializer : function(config) {
        var self = this;
        // Attach listener to all group mode buttons.
        Y.all('.moodle-actionmenu .menu').each(function(icons) {
            self.processIcons(icons);
        });
    },
    processIcons : function(icons) {
        icons.all('a').each(function(tag) {
            var action = tag.getData('action');
            if(action != undefined && action.match(/groups/)) {
                // Set title attribute so that the toolbox JS will handle it properly.
                tag.setAttribute('title', tag.one('span').getHTML());

                tag.on('click', function(e, tag) {
                    // Figure out current mode.
                    var groupmode = parseInt(tag.getData('nextgroupmode'), 10),
                        newtitle = '',
                        newtitlestr;

                    if (groupmode === M.course.resource_toolbox.GROUPS_NONE) {
                        newtitle = 'groupsnone';
                    } else if (groupmode === M.course.resource_toolbox.GROUPS_SEPARATE) {
                        newtitle = 'groupsseparate';
                    } else if (groupmode === M.course.resource_toolbox.GROUPS_VISIBLE) {
                        newtitle = 'groupsvisible';
                    }
                    newtitlestr = M.util.get_string(newtitle, 'moodle'),
                    newtitlestr = M.util.get_string('clicktochangeinbrackets', 'moodle', newtitlestr);

                    // Change the UI.
                    tag.one('span').setHTML(newtitlestr);

                }, this, tag);
            }
        });
    }
};
// Make this into a fully fledged YUI module.
Y.extend(ActionMenu, Y.Base, ActionMenu.prototype, {
    NAME : 'Decaf theme action menu enhancments',
    ATTRS : {
        // No attributes at present.
    }
});

M.theme_decaf = M.theme_decaf || {};
M.theme_decaf.initActionMenu = function(cfg) {
    return new ActionMenu(cfg);
}

}, '@VERSION@', {requires:['base','node','moodle-course-toolboxes']});