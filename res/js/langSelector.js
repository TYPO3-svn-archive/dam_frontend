

$(document).ready(function() {
    $('.tx_dam_fe_LANGSELECTOR li a').click(function() {
        var langClass;
        langElements = new Array();
        langClass = $(this).parent().attr("class");
        var langElements = langClass.split("_");
        langElements.reverse();
        lang = langElements[0]
        $('.tx_damfe_element').each(function() {
            if (lang == 'ALL') {
                $(this).fadeIn('slow').removeClass('hidden');
            } else {
                if(!$(this).hasClass('tx_damfe_'+lang)) {
                    $(this).fadeOut('normal').addClass('hidden');
                } else {
                    $(this).fadeIn('slow').removeClass('hidden');
                }
            }
        });
        return false;
    });

});