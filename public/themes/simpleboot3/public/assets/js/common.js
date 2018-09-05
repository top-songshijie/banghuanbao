<<<<<<< HEAD
/***禁止滑动***/
function stop() {
    document.body.style.overflow = 'hidden';
    // document.addEventListener("touchmove", mo, false); //禁止页面滑动
    $("body").on("touchmove",function(event){
        event.preventDefault();
    }, false)
}

/***取消滑动限制***/
function move() {
    document.body.style.overflow = ''; //出现滚动条
    // document.removeEventListener("touchmove", mo, false);
    $("body").off("touchmove");
}
function toast(that) {
    var $toast = $('#toast');
    stop()
    $toast.find(".weui-toast__content").html(that)
    if ($toast.css('display') != 'none') return;
    $toast.fadeIn(100);
    setTimeout(function () {
        $toast.fadeOut(100);
        move()
    }, 2000);
}
//到即时
var countdown=60;
function settime(val) {
    if (countdown == 0) {
        val.removeAttribute("disabled");
        val.value="获取验证码";
        countdown = 60;
    } else {
        val.setAttribute("disabled", true);
        val.value= countdown +"s";
        countdown--;
        setTimeout(function() {
            settime(val)
        },1000)
    }

}

//选择头像上传
function setImagePreview(avalue,obj) {
    var docObj = document.getElementById("doc");
    //img
    var imgObjPreview = document.getElementById("preview");
    //div
    var divs = document.getElementById(obj);
    if (docObj.files && docObj.files[0]) {
        //火狐下，直接设img属性
        imgObjPreview.style.display = 'block';
        imgObjPreview.style.width = '100'+"%";
        imgObjPreview.style.height = '100%';
        //imgObjPreview.src = docObj.files[0].getAsDataURL();
        //火狐7以上版本不能用上面的getAsDataURL()方式获取，需要一下方式
        imgObjPreview.src = window.URL.createObjectURL(docObj.files[0]);
    } else {
        //IE下，使用滤镜
        docObj.select();
        var imgSrc = document.selection.createRange().text;
        var localImagId = document.getElementById("localImag");
        //必须设置初始大小
        localImagId.style.width = "100px";
        localImagId.style.height = "100px";
        //图片异常的捕捉，防止用户修改后缀来伪造图片
        try {
            localImagId.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale)"
            localImagId.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = imgSrc;
        } catch(e) {
            alert("您上传的图片格式不正确，请重新选择!");
            return false;
        }
        imgObjPreview.style.display = 'none';
        document.selection.empty();
    }
    return true;
=======
/***禁止滑动***/
function stop() {
    document.body.style.overflow = 'hidden';
    // document.addEventListener("touchmove", mo, false); //禁止页面滑动
    $("body").on("touchmove",function(event){
        event.preventDefault();
    }, false)
}

/***取消滑动限制***/
function move() {
    document.body.style.overflow = ''; //出现滚动条
    // document.removeEventListener("touchmove", mo, false);
    $("body").off("touchmove");
}
function toast(that) {
    var $toast = $('#toast');
    stop()
    $toast.find(".weui-toast__content").html(that)
    if ($toast.css('display') != 'none') return;
    $toast.fadeIn(100);
    setTimeout(function () {
        $toast.fadeOut(100);
        move()
    }, 2000);
}
//到即时
var countdown=60;
function settime(val) {
    if (countdown == 0) {
        val.removeAttribute("disabled");
        val.value="获取验证码";
        countdown = 60;
    } else {
        val.setAttribute("disabled", true);
        val.value= countdown +"s";
        countdown--;
        setTimeout(function() {
            settime(val)
        },1000)
    }

}

//选择头像上传
function setImagePreview(avalue,obj) {
    var docObj = document.getElementById("doc");
    //img
    var imgObjPreview = document.getElementById("preview");
    //div
    var divs = document.getElementById(obj);
    if (docObj.files && docObj.files[0]) {
        //火狐下，直接设img属性
        imgObjPreview.style.display = 'block';
        imgObjPreview.style.width = '100'+"%";
        imgObjPreview.style.height = '100%';
        //imgObjPreview.src = docObj.files[0].getAsDataURL();
        //火狐7以上版本不能用上面的getAsDataURL()方式获取，需要一下方式
        imgObjPreview.src = window.URL.createObjectURL(docObj.files[0]);
    } else {
        //IE下，使用滤镜
        docObj.select();
        var imgSrc = document.selection.createRange().text;
        var localImagId = document.getElementById("localImag");
        //必须设置初始大小
        localImagId.style.width = "100px";
        localImagId.style.height = "100px";
        //图片异常的捕捉，防止用户修改后缀来伪造图片
        try {
            localImagId.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale)"
            localImagId.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = imgSrc;
        } catch(e) {
            alert("您上传的图片格式不正确，请重新选择!");
            return false;
        }
        imgObjPreview.style.display = 'none';
        document.selection.empty();
    }
    return true;
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
}