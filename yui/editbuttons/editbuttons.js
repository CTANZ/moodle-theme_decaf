YUI.add('moodle-theme_decaf-editbuttons', function(Y) {

/**
 * Splash theme colour switcher class.
 * Initialise this class by calling M.theme_splash.init
 */
var EditButtons = function() {
    EditButtons.superclass.constructor.apply(this, arguments);
};
EditButtons.prototype = {
    initializer : function(config) {
        var editbutton = Y.Node.create('<a></a>');
        editbutton.set('innerHTML', 'Edit');
        editbutton.addClass('decaf-editbutton');
        // Find all sets of icons and convert them to edit buttons
        Y.all('.commands').each(function(icons) {
            if(icons.getComputedStyle('display')=='none' && (icons.all('a').getDOMNodes().length && icons.ancestor('.path-mod-forum #region-main')==null)) {
                var thisbutton = editbutton.cloneNode(true);
                thisbutton.on('click', function(e, button) {
                    e.preventDefault();
                    button.ancestor().toggleClass('active');
                    button.ancestor('li').toggleClass('decaf-editbutton-active-module');
                }, this, thisbutton);
                icons.all('a').each(function(tag) {
                    var caption = tag.get('title') || tag.one('img').get('title') || tag.one('img').get('alt');
                    tag.one('img').removeAttribute('hspace');
                    tag.append('<span>' + caption + '</span>');
                });
                icons.wrap('<div class="decaf-editbutton-wrap"></div>').insert(thisbutton, icons);
            }
        });
        try {
            M.core_dock.getPanel().on('dockpanel:beforeshow', function(e) {
                var thisbutton = editbutton.cloneNode(true);
                var icons = this.one('.dockeditempanel_hd .commands');
                var closeicon = icons.one('.hidepanelicon');
                thisbutton.on('click', function(e, button) {
                    e.preventDefault();
                    button.ancestor().toggleClass('active');
                    button.ancestor('li').toggleClass('decaf-editbutton-active-module');
                }, this, thisbutton);
                icons.wrap('<div class="decaf-editbutton-wrap"></div>').insert(thisbutton, icons);
                if(closeicon) {
                    thisbutton.ancestor().insertBefore(icons.removeChild(closeicon), thisbutton);
                }
            }, M.core_dock.getPanel())
            M.core_dock.getPanel().on('dockpanel:beforehide', function(e) {
                var icons = this.one('.dockeditempanel_hd .commands');
                var closeicon = this.one('.decaf-editbutton-wrap .hidepanelicon');
                if(closeicon) {
                    icons.appendChild(closeicon.ancestor().removeChild(closeicon));
                }
            }, M.core_dock.getPanel())
        } catch(x) {}
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