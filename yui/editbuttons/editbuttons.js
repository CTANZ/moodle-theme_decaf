YUI.add('moodle-theme_decaf-editbuttons', function(Y) {

/**
 * Splash theme colour switcher class.
 * Initialise this class by calling M.theme_splash.init
 */
var EditButtons = function() {
    EditButtons.superclass.constructor.apply(this, arguments);
};
EditButtons.prototype = {
    hasOverriddenDndUpload: false,
    editbutton: false,
    activeButton: null,
    initializer : function(config) {
        var self = this;
        if(!this.editbutton) {
            this.editbutton = Y.Node.create('<a href="#"></a>');
            this.editbutton.set('innerHTML', M.util.get_string('edit', 'moodle'));
            this.editbutton.addClass('decaf-editbutton');
        }
        // Find all sets of icons and convert them to edit buttons
        Y.all('.commands').each(function(icons) {
            if(icons.getComputedStyle('display')=='none' && (!icons.all('a').isEmpty() && icons.ancestor('.path-mod-forum #region-main')==null)) {
                self.processIcons(icons);
            }
        });

        // Delegated click handler for all edit buttons
        Y.delegate('click', function(e) {
            e = e || window.event;
            e.preventDefault();
            this.toggleButton(e.target);
        },
        'body', 'a.decaf-editbutton', this);

        try {
            M.core_dock.on('dock:panelresizestart', function(e) {
                var item = M.core_dock.getActiveItem();
                if (item.decaf_editbutton_done) {
                    this.all('.dockeditempanel_hd .hidepanelicon').remove();
                    item.fire('dockeditem:drawcomplete');
                    return;
                }
                var icons = this.one('.dockeditempanel_hd .commands');
                icons.all('.moveto span').remove()

                // Don't bother if it's only the undock and close icons
                if (icons.get('children').size()===2) {
                    icons.addClass('dock-commands');
                } else {
                    self.wrapButton(icons, self.editbutton.cloneNode(true));
                    var wrap = icons.ancestor();
                    // Put dock controls back outside edit button
                    wrap.append(icons.all('.moveto').remove());
                    wrap.append(icons.all('.hidepanelicon').remove());
                    item.commands = wrap.cloneNode(true);
                }
                item.decaf_editbutton_done = true;
            }, M.core_dock.getPanel());

            var attachRemoveHandler = function(item) {
                item.on('dockeditem:itemremoved', function() {
                    var button = this.commands.ancestor('.header').one('.decaf-editbutton-wrap');
                    if (button) button.remove();
                    if (!this.decaf_editbutton_done && this.commands.hasChildNodes()) {
                        self.wrapButton(this.commands, self.editbutton.cloneNode(true));
                    }
                }, item);
            }
            M.core_dock.on('dock:itemadded', attachRemoveHandler, M.core_dock);
            Y.Array.each(M.core_dock.items, attachRemoveHandler);
        } catch(x) {}

        // Horribly nasty hack, since nothing in the dndupload chain fires any events we can listen for.
        // Since the dndupload module isn't there when we initialise, override its add_editing function
        // when we first see a "drop" on a section.
        // (could also be done with delays/setTimeout, but that's less reliable due to timing issues)
        var sections = Y.all('li.section.main');
        sections.each( function(el) {
            Y.on('drop', function(el) {
                if (!this.hasOverriddenDndUpload) {
                    var orig = M.course_dndupload.add_editing,
                    self = this;
                    M.course_dndupload.add_editing = function(elementid) {
                        self.processIcons(Y.one('#'+elementid+' .commands'));
                        orig(elementid);
                    }
                    this.hasOverriddenDndUpload = true;
                }
            }, el, this);
        }, this);
    },
    wrapButton : function(icons, button) {
        try{
            icons.wrap('<div class="decaf-editbutton-wrap"></div>').insert(button, icons);
        } catch(x) {
            // Fallback for old versions of YUI without Node.wrap() (MDL20)
            var wrapper = Y.DOM.create('<div class="decaf-editbutton-wrap"></div>');
            icons.ancestor().replaceChild(wrapper, icons);
            wrapper = Y.one(wrapper);
            wrapper.appendChild(icons);
            wrapper.insert(button, icons);
        }
    },
    processIcons : function(icons) {
        var thisbutton = this.editbutton.cloneNode(true);
        icons.all('a').each(function(tag) {
            var icon = tag.one('img');
            var caption = tag.get('title') || icon.get('title') || icon.get('alt');
            icon.removeAttribute('hspace');
            tag.append('<span>' + caption + '</span>');
            if(icon.get('src').match(/hide$/) || icon.get('src').match(/show$/) || icon.get('src').match(/group[nvs]$/)) {
                tag.on('click', function(e, tag) {
                    var icon = tag.one('img');
                    var caption = tag.get('title') || icon.get('title') || icon.get('alt');
                    icon.removeAttribute('hspace');
                    tag.one('span').set('innerHTML', caption);
                }, this, tag);
            }
        });
        this.wrapButton(icons, thisbutton);
    },
    toggleButton : function(button) {
        if(this.activeButton != null && this.activeButton != button) {
            this.toggleButton(this.activeButton);
            this.activeButton = button;
        } else if(this.activeButton == button) {
            this.activeButton = null;
        } else {
            this.activeButton = button;
        }
        button.ancestor().toggleClass('active');
        var mod = button.ancestor('li');
        if(mod) mod.toggleClass('decaf-editbutton-active-module');
    }
};
// Make the colour switcher a fully fledged YUI module
Y.extend(EditButtons, Y.Base, EditButtons.prototype, {
    NAME : 'Decaf theme edit buttoniser',
    ATTRS : {
        // No attributes at present
    }
});
// Our splash theme namespace
M.theme_decaf = M.theme_decaf || {};
// Initialisation function for the colour switcher
M.theme_decaf.initEditButtons = function(cfg) {
    return new EditButtons(cfg);
}

}, '@VERSION@', {requires:['base','node']});