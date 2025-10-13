<?php
//for assending mode, show the comment box at the top
if (!$sort_as_decending) {
    foreach ($comments as $comment) {
        echo view("tickets/comment_row", array("comment" => $comment));
    }
}
?>

<div id="comment-form-container">
    <?php echo form_open(get_uri("tickets/save_comment"), array("id" => "comment-form", "class" => "general-form", "role" => "form")); ?>
    <div class="p15 d-flex">
        <div class="flex-shrink-0 hidden-xs">
            <div class="avatar avatar-md pr15">
                <img src="<?php echo get_avatar($login_user->image); ?>" alt="..." />
            </div>
        </div>

        <div class="w-100">
            <div id="ticket-comment-dropzone" class="post-dropzone form-group">
            <input type="hidden" id="is_comment" name="is_comment" value="0">
                <input type="hidden" name="ticket_id" value="<?php echo $ticket_info->id; ?>">
                <input type="hidden" id="is-note" name="is_note" value="0">
                <?php
                echo form_textarea(array(
                    "id" => "description",
                    "name" => "description",
                    "class" => "form-control",
                    "style" => "height: 200px",
                    "value" => process_images_from_content(get_setting('user_' . $login_user->id . '_signature'), false),
                    "placeholder" => app_lang('write_a_comment'),
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                    "data-rich-text-editor" => true
                ));
                ?>
                <?php echo view("includes/dropzone_preview"); ?>
                <footer class="card-footer b-a clearfix ticket-view-footer-button">
                    <div class="float-start"><?php echo view("includes/upload_button"); ?></div>

                    <?php
                    if ($login_user->user_type === "staff" && $view_type != "modal_view") {
                        echo modal_anchor(get_uri("tickets/insert_template_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('insert_template'), array("class" => "btn btn-default float-start round ml10", "title" => app_lang('insert_template'), "style" => "color: #7988a2", "data-post-ticket_type_id" => $ticket_info->ticket_type_id, "id" => "insert-template-btn"));
                    }
                    ?>

                    <div class="float-end">
                        <?php if ($login_user->user_type === "staff") { ?>
                            <button id="save-as-note-button" class="btn btn-info text-white" type="button" data-bs-toggle="tooltip" title="<?php echo app_lang('client_will_not_see_any_notes') ?>"><i data-feather='message-circle' class='icon-16'></i> <?php echo app_lang("save_as_note"); ?></button>
                        <?php } ?>
                        <button id="save-ticket-comment-button" class="btn btn-primary ml5" type="submit"><i data-feather='send' class='icon-16'></i> <?php echo app_lang("post_comment"); ?></button>
                    </div>
                </footer>
            </div>
        </div>

    </div>
    <?php echo form_close(); ?>
</div>
<style>
    @keyframes blink {
    0% { background-color: transparent !important; }
    50% { background-color: blue !important; }
    100% { background-color: transparent !important; }
}

.blinking-effect {
    background: #def2ff !important;
    animation: blink 1s infinite !important;
}
.activeReplace{
    background:rgb(1, 121, 200) !important;
    color: #fff !important;
}
</style>
<script>

    $(document).on("click", ".replace_comment", function() {
        var valIsComment = $("#is_comment").val();
        if(valIsComment == 0){
            $(this).addClass('activeReplace');
            // Obtener el ID y la descripción del elemento clickeado
            let commentID = $(this).data("id");
            let description = $(this).data("description");
            console.log(description);

            $("#description").val(description); // Si quieres solo mostrar el texto
            $("#is_comment").val(commentID);

            let parentContainer = $(this).closest(".ticket-comment-container");
            parentContainer.removeClass("bg-white");
            parentContainer.addClass("blinking-effect");

        }else{
            $(this).removeClass('activeReplace');
            let parentContainer = $(this).closest(".ticket-comment-container");
            parentContainer.addClass("bg-white");
            parentContainer.removeClass("blinking-effect");
            $("#is_comment").val(0);
            $("#description").val("");
        }
        
    });

    $(document).on("dblclick", ".commentTicket", function() {
        let commentID = $(this).data("id"); // Obtener el ID del comentario
        let currentText = $(this).text();

        // Crear un textarea para editar el comentario
        let textarea = $("<textarea>")
            .val(currentText)
            .addClass("editableText form-control")
            .css({
                "width": "100%",
                "height": "100px"
            })
            .data("id", commentID); // Pasar el ID al textarea

        // Crear un botón para guardar el comentario
        let saveButton = $("<button>")
            .text("Guardar")
            .addClass("saveCommentButton btn btn-primary")
            .css({
                "margin-top": "10px",
                "padding": "5px 15px"
            });

        // Reemplazar el comentario por el textarea y el botón
        $(this).replaceWith(textarea);
        textarea.after(saveButton);
        textarea.focus();
    });

    $(document).on("click", ".saveCommentButton", function() {
        let buttonClicked = $(this)
        let textarea = $(this).prev(".editableText"); // Obtener el textarea anterior al botón
        let newText = textarea.val();
        let commentID = textarea.data("id"); // Obtener ID del comentario

        // Reemplazar saltos de línea con '\n' para mantenerlos en el texto
        newText = newText.replace(/\n/g, '\n');
        
        $.ajax({
            url: "<?php echo get_uri("tickets/update_comment") ?>",
            type: "POST",
            data: {
                id: commentID,
                comment: newText
            },
            dataType: "json",
            success: function(result) {
                if (result.success) {
                    // Crear un nuevo párrafo con el texto actualizado
                    let paragraph = $("<p>")
                        .text(newText) // Usar .text() para mantener los saltos de línea con \n
                        .addClass("commentTicket")
                        .attr("data-id", commentID)
                        
                        .css("white-space", "pre-line"); // Usar white-space: pre-line para mostrar saltos de línea

                        let parentContainer = buttonClicked.closest(".ticket-comment-container");
                        let button = parentContainer.find(".replace_comment");
                        button.attr("data-description", newText);
                    // Reemplazar el textarea y el botón con el párrafo
                    textarea.replaceWith(paragraph);
                    $(".saveCommentButton").remove(); 
                   
                    // Recargar la página y desplazarse hasta el comentario actualizado
                }
            },
            error: function() {
                alert("Hubo un error al actualizar el comentario.");
            }
        });
    });

    

    $(document).on("click", "#save-ticket-comment-button", function () {
        let is_comment = $("#is_comment").val(); // Obtener el valor desde el campo con id="is_comment"
        if(is_comment != 0){
            $("#ticket-details-section").removeClass('show');
            setTimeout(function(){
            window.location.reload();
        },100);
        }
    
    });

    $(document).on("click", "#save-as-note-button", function () {
        let is_comment = $("#is_comment").val(); // Obtener el valor desde el campo con id="is_comment"
        if(is_comment != 0){
            $("#ticket-details-section").removeClass('show');
            setTimeout(function(){
            window.location.reload();
            },50);
        }
    });
</script>



<?php
//for decending mode, show the comment box at the bottom
if ($sort_as_decending) {
    foreach ($comments as $comment) {
        echo view("tickets/comment_row", array("comment" => $comment));
    }
}

?>