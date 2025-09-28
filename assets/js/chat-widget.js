jQuery(document).ready(function($){

    const $bubble = $('#growthnow-bubble');
    const $widget = $('#growthnow-chat-widget');
    const $body = $('#growthnow-chat-body');
    const $input = $('#growthnow-chat-input');
    const $send = $('#growthnow-chat-send');
    const $typing = $('#growthnow-typing-indicator');

    // Toggle chat widget
    $bubble.click(() => $widget.toggleClass('active'));
    $('#growthnow-chat-close').click(() => $widget.removeClass('active'));

    // Append message
    function appendMessage(msg, isUser){
        const msgClass = isUser ? 'user-msg' : 'ai-msg';
        $body.append(`<div class="${msgClass}">${msg}</div>`);
        $body.scrollTop($body[0].scrollHeight);
    }

    // Auto-resize textarea
    $input.on('input', function(){
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });

    // Send message
    function sendMessage(){
        const msg = $input.val().trim();
        if(!msg) return;
        appendMessage(msg, true);
        $input.val('').css('height','auto');
        $typing.show();

        $.post(growthnow_ajax.ajax_url, {
            action: 'growthnow_chat',
            nonce: growthnow_ajax.nonce,
            message: msg
        }, function(response){
            $typing.hide();
            if(response.success) appendMessage(response.data, false);
        });
    }

    $send.click(sendMessage);
    $input.keypress(function(e){
        if(e.which === 13 && !e.shiftKey){ e.preventDefault(); sendMessage(); }
    });

    // Add to cart
    $(document).on('click','.growthnow-add-to-cart', function(){
        const productId = $(this).data('product-id');
        $.post(growthnow_ajax.ajax_url,{
            action:'growthnow_add_to_cart',
            product_id: productId,
            nonce: growthnow_ajax.nonce
        }, function(response){
            if(response.success){
                $('<div class="cart-toast">✔ '+response.data+'</div>').appendTo('body')
                    .fadeIn(300).delay(1500).fadeOut(400, function(){ $(this).remove(); });
            }
        });
    });

});
