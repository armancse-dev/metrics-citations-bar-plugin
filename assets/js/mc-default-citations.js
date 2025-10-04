(function($){
    $(document).ready(function(){

        // Only for the citations repeater
        var repeater = $('.acf-field-repeater[data-name="citations"]');

        if(repeater.length && repeater.find('> .acf-table > tbody > tr').length === 0){
            
            var defaults = [
                { style: 'APA', citation_text: 'Author, A. A. (Year). Title of work. Publisher.' },
                { style: 'MLA', citation_text: 'Author Lastname, Firstname. Title of Book. Publisher, Year.' },
                { style: 'Chicago', citation_text: 'Author Lastname, Firstname. Title of Book. Place: Publisher, Year.' },
                { style: 'Harvard', citation_text: 'Author, A.A., Year. Title of book. Edition. Place of Publisher.' }
            ];

            defaults.forEach(function(row){
                // Trigger ACF add row button
                repeater.find('> .acf-actions > .acf-button').trigger('click');

                var lastRow = repeater.find('> .acf-table > tbody > tr:last');

                lastRow.find('[data-name="style"] input').val(row.style);
                lastRow.find('[data-name="citation_text"] input').val(row.citation_text);
            });
        }
    });
})(jQuery);
