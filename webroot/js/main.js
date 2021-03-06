(function($, document, corahn_rin, window){
    window.base_url = corahn_rin;
    var base_url = window.base_url;

    /**
     * Efface la sélection actuelle sur la page
     *
     * @author Pierstoval
     */
    function clearSelection() {
        if (document.selection) {
            document.selection.empty();
        } else if (window.getSelection) {
            window.getSelection().removeAllRanges();
        }
    }
    window.clearSelection = clearSelection;

    //$(document).ready(function(){ console.clear(); });

    /**
     * Envoie les informations du formulaire d'une étape à la session
     *
     * @param values Un tableau de données à transférer
     * @param action La destination de la page, sera le lien dans la balise de l'étape suivante
     * @param empty Si true, on envoie des données vides à la page pour annuler l'effet du formulaire
     * @param show_msg Si true, on affiche le résultat de la requête dans la balise id="err"
     * @author Pierstoval
     */
    function sendMaj(values, action, empty, show_msg) {
        if (empty !== true) {
            $('#gen_send').html('<img src=\"'+base_url.replace(/(\/fr|\/en)/gi,'')+'/img/ajax-loader.gif\" />').css('visibility', 'visible');
        } else {
            values['empty'] = '1';
            $('#gen_send').attr('href', '#').html('').css('visibility', 'hidden');
        }
        if (empty !== true) {  }

        if (window.xhr && window.xhr.ajaxStop) {
            window.xhr.ajaxStop();
        }
        window.xhr = $.ajax({
            url : base_url+'/ajax/aj_genmaj.php',
            type : 'post',
            data : values,
            success : function(msg) {
                if (empty !== true) {
                    $('#gen_send').delay(1).attr('href', base_url+action).html(nextsteptranslate).css('visibility', 'visible');
                } else {
                    $('#gen_send').delay(1).attr('href', '#').html(nextsteptranslate).css('visibility', 'hidden');
                }
                if (show_msg === true) {
                    $('#err').html(msg).show();
                }
            }
        });
    }
    window.xhr = null;
    window.sendMaj = sendMaj;

    var ky = [];
    var ko = '38,38,40,40,37,39,37,39,66,65';
    var txt = 'Just decode the binary string in the source code of this page !';
    $(document).keydown(function(e) {
            ky.push(e.keyCode);
            if (ky.toString().indexOf(ko) >= 0){
                alert(txt);
                ky = [];
            }
        }
    );

    $('button.showhidden').click(function(){
        $(this).next('.hid').slideToggle(400);
    });


})(jQuery, document, corahn_rin, window);