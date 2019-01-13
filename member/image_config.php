?>﻿<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script>
    KindEditor.ready(function(K) {
        var editor = K.editor({
            uploadJson : '/member/upload_json.php?TableField=stores',
            fileManagerJson : '/member/file_manager_json.php',
            showRemote : true,
            allowFileManager : true,
        });
        K('#ImgUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#ImgPath').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#ImgPath').val(url);
                        K('#ImgDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        K('#BannerUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#BannerPath').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#BannerPath').val(url);
                        K('#BannerDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        //游戏中心事件
        K.create('textarea[name="Rules"]', {
            themeType : 'simple',
            filterMode : false,
            uploadJson : '/member/upload_json.php?TableField=games',
            fileManagerJson : '/member/file_manager_json.php',
            allowFileManager : true,
            afterBlur:function(){this.sync()},
            items : [
                'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
                'removeformat', 'undo', 'redo', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
        });
        K('#ReplyImgUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#ReplyImgPath').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#ReplyImgPath').val(url);
                        K('#ReplyImgDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        K('#AttentionImgPathUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#AttentionImgPath').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#AttentionImgPath').val(url);
                        K('#AttentionImgPathDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        K('#_GameConfigImgUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#_GameConfigImg').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#_GameConfigImg').val(url);
                        K('#_GameConfigImgDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });

        K('#_GameConfigBgImgUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#_GameBgConfigImg').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#_GameBgConfigImg').val(url);
                        K('#_GameConfigBgImgDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });

        K('#GameImgcardbgUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#imgcardbg').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#imgcardbg').val(url);
                        K('#GameImgcardbgDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        K('#MusicPathUpload').click(function(){
            editor.loadPlugin('insertfile', function(){
                editor.plugin.fileDialog({
                    imageUrl : K('#MusicPath').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#MusicPath').val(url);
                        K('#BackgroundMusic').val(url);
                        editor.hideDialog();
                    }
                });
            });
        });
        <?php for($i=0; $i<5;$i++){  ?>
        K('#HomeFileUpload_<?=$i ?>').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#ImgPath_<?=$i ?>').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#ImgPath_<?=$i ?>').val(url);
                        K('#PagesPicDetail_<?=$i ?>').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        <?php } ?>
        K('#PagesPicUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#PagesPic').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#PagesPic').val(url);
                        K('#PagesPicDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        K('#GameImggamebgUpload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#imggamebg').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#imggamebg').val(url);
                        K('#GameImggamebgDetail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        <?php
        for($i=1;$i<14;$i++) {
            if($i<6 && ($i!=4 || $i!=2)){
        ?>
        K('#GameImg<?=$i ?>Upload').click(function(){
            editor.loadPlugin('image', function(){
                editor.plugin.imageDialog({
                    imageUrl : K('#Property<?=$i ?>').val(),
                    clickFn : function(url, title, width, height, border, align){
                        K('#Property<?=$i ?>').val(url);
                        K('#Img<?=$i ?>Detail').html('<img src="'+url+'" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        <?php
            }
            if($i>2){
        $pow = pow(2, $i);
        ?>
        K('#GameImg<?=$pow ?>Upload').click(function () {
            editor.loadPlugin('image', function () {
                editor.plugin.imageDialog({
                    imageUrl: K('#Property<?=$pow ?>').val(),
                    clickFn: function (url, title, width, height, border, align) {
                        K('#Property<?=$pow ?>').val(url);
                        K('#Img<?=$pow ?>Detail').html('<img src="' + url + '" />');
                        editor.hideDialog();
                    }
                });
            });
        });
        <?php
            }
        }
        ?>
    })
</script>
<style>
    .r_con_form .rows .input .upload_file .img { clear:both; margin-top:20px;}
    .r_con_form .rows .input .upload_file .img img { max-width:320px;max-height:100px;}
    .r_con_form .rows .input .upload_file .tips {clear:both;}
</style>