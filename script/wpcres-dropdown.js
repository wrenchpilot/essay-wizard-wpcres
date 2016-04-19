// Create tinyMCE plugin for wpCRES Assingments dropdown menu
tinymce.PluginManager.add( 'wpcres' , function( editor ){
	//Grab the list of wpCRES assignments
	//Put into JSON array
	var value_array = [];
	for (var i in my_wpcres_posts){
		value_array.push({text:my_wpcres_posts[i][0],value: my_wpcres_posts[i][1]});
	}
	
	//Add button
    editor.addButton('wpcres', {
        type: 'listbox',
        text: 'wpCRES Assignments',
        onselect: function(ed) {
            tinymce.execCommand('mceInsertContent',false,'[wpcres id="'+this.value()+'"]');
        },
        values: value_array
    });
});
