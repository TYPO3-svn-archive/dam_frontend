$(document).ready(function() {
    $('div.productBox').each(function() {
        if($(this).hasClass('paediatric,')) {
            $(this).removeClass('paediatric,');
            $(this).addClass('paediatric');
        }
        if($(this).hasClass('mri,')) {
            $(this).removeClass('mri,');
            $(this).addClass('mri');
        }
        if($(this).hasClass('neonatal,')) {
            $(this).removeClass('neonatal,');
            $(this).addClass('neonatal');
        }
        if($(this).hasClass('clinical-icu,')) {
            $(this).removeClass('clinical-icu,');
            $(this).addClass('clinical-icu');
        }
        if($(this).hasClass('mobile-icu,')) {
            $(this).removeClass('mobile-icu,');
            $(this).addClass('mobile-icu');
        }
        if($(this).hasClass('subacute-care,')) {
            $(this).removeClass('subacute-care,');
            $(this).addClass('subacute-care');
        }
        if($(this).hasClass('adult,')) {
            $(this).removeClass('adult,');
            $(this).addClass('adult');
        }
    });
});

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