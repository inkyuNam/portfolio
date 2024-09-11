//<![CDATA[
$(document).on('mouseenter', '.info_area .info_tbl .afimg_wr', function(){
    // $('.after_image').stop().fadeOut();
    var isImage = $(this).find('.after_image').children().attr('src');
    if( $(this).find('.after_image').length > 0 && isImage.indexOf('noimage') == -1 ){
        $(this).find('.after_image').stop().fadeIn();
    }
});
$(document).on('mouseout', '.info_area .info_tbl .afimg_wr', function(){
    // $('.after_image').stop().fadeOut();
    var isImage = $(this).find('.after_image').children().attr('src');
    $(this).find('.after_image').stop().fadeOut();
});
/*
$(document).on('mouseover', function(e){
    var target = e.target.className;
    if(target.indexOf('afimg_wr') == -1){
        $('.after_image').stop().fadeOut();
    }
});
*/

$(document).on('click', '.tab_area ul li a', function(){
    let idx = $(this).parent().index();
    $(this).parent().siblings().removeClass('on');
    $(this).parent().addClass('on');
    $('#sub.product_view .btm_area .info_area > div').css('display', 'none');
    $('#sub.product_view .btm_area .info_area > div').eq(idx).css('display', 'block');
});

$(document).on('change', '#addFile', function(){
    var fileVal = $(this).val();
    var ext = fileVal.split('.').pop().toLowerCase(); //확장자분리
    var maxSize = 30 * 1024 * 1024; // 30MB
    var fileSize = $(this)[0].files[0].size;

    if($.inArray(ext, ['psd']) == -1) {
        alert('psd파일만 업로드 할 수 있습니다.');
        $(this).val('');
        $('#filename').val('');
        return;
    } else if(fileSize > maxSize){
        alert('디자인파일 사이즈는 30MB 이내로 등록 가능합니다.');
        $(this).val('');
        $('#filename').val('');
        return;
    }else{
        $('#filename').val($(this)[0].files[0].name);
        $('#filename').css('display', 'block');
    }
});

//숫자만 출력
function isOnlyNumber(obj) {
    var exp = /[^0-9]/g;
    if (exp.test(obj.value)) {
        alert("숫자만 입력가능합니다.");
        obj.value = "";
        obj.focus();
    }
}
//]]>


// 공급가액 계산
function select_cover_type(obj)
{
    let type = obj.value;
    const hard_input = document.getElementById('hard_cover_quantity');
    const soft_input = document.getElementById('soft_cover_quantity');
    switch(type){
        case 'hard':{
            $('.hard-type').css('display', 'block');
            $('.soft-type').css('display', 'none');
            $('.select-cover').css('display','block');
            $('.none-cover-select').css('display', 'none');
            soft_input.value = '';
            break;
        }
        case 'soft':{
            $('.hard-type').css('display', 'none');
            $('.soft-type').css('display', 'block');
            $('.select-cover').css('display','block');
            $('.none-cover-select').css('display', 'none');
            hard_input.value = '';
            break;
        }
        case 'both':{
            $('.hard-type').css('display', 'block');
            $('.soft-type').css('display', 'block');
            $('.select-cover').css('display','block');
            $('.none-cover-select').css('display', 'none');
            break;
        }
        default:{
            $('.hard-type').css('display', 'none');
            $('.soft-type').css('display', 'none');
            $('.select-cover').css('display','none');
            $('.none-cover-select').css('display', 'block');
            soft_input.value = '';
            hard_input.value = '';
            break;
        }
    }
    updatePrice();
}




function select_hard_cover_color(obj)
{
    let color = obj.value;
    if(color === '기타'){
        $('#ex_color').css('display','none');
        $('#ex_color').val('');
        $('#select_color_hard_cover_img').attr('src', cover_etc);
    }else if(color === '검정') {
        $('#ex_color').css('display','none');
        $('#ex_color').val('');
        $('#select_color_hard_cover_img').attr('src', hard_black);
    }else if(color === '진곤') {
        $('#ex_color').css('display','none');
        $('#ex_color').val('');
        $('#select_color_hard_cover_img').attr('src', hard_navy);
    }else{
        $('#select_color_hard_cover_img').attr('src', '');
    }
}

function select_hard_cover_gs(obj)
{
    const gs = obj.value;
    if(gs === '금박'){
        $('#select_gs_hard_cover_img').attr('src', hard_gold);
    }else if(gs === '은박'){
        $('#select_gs_hard_cover_img').attr('src', hard_silver);
    }else{
        $('#select_gs_hard_cover_img').attr('src', '');
    }
}

function select_soft_color(obj)
{
    const color = obj.value;
    if(color === '회색'){
        $('#select_color_soft_cover_img').attr('src', soft_gray);
    }else if(color === '백색'){
        $('#select_color_soft_cover_img').attr('src', soft_white);
    }else{
        $('#select_color_soft_cover_img').attr('src', '');
    }
}

function select_content_color(obj)
{
    const color = obj.value;
    if(color === '백색'){
        $('#select_content_color_img').attr('src', content_white);
    }else if(color === '미색'){
        $('#select_content_color_img').attr('src', content_ivory);
    }else{
        $('#select_content_color_img').attr('src', '');
    }
}


function thesis_submit(frm) {

    if(frm.mem_id.value === ''){
        alert('로그인 후 이용할 수 있습니다.');
        return false;
    }

    if(frm.cover_size.value === ''){
        alert('사이즈 선택하세요');
        return false;
    }

    let cover_type = frm.cover_type.value;

    if(cover_type === ''){
        alert('제작형태를 선택하세요');
        return false;
    }else{

        let hq = $('#hard_cover_quantity').val() || 0;
        let sq = $('#soft_cover_quantity').val() || 0;
        let hard_color = $('#hard_cover_color').val();
        let hard_gs = $('#hard_cover_gs').val();
        let soft_color = $('#soft_cover_color').val();

        if(cover_type === 'hard'){
            if(hq < 10){
                alert('부수 수량을 입력하세요(최소10부)');
                frm.hard_cover_quantity.focus();
                return false;
            }
            if(hard_color === '' && hard_gs === ''){
                alert('표지옵션을 하나 이상 선택하세요');
                return false;
            }
        }else if(cover_type === 'soft'){
            if(sq < 10){
                alert('부수 수량을 입력하세요(최소10부)');
                frm.soft_cover_quantity.focus();
                return false;
            }
            if(soft_color === ''){
                alert('표지옵션을 선택하세요');
                return false;
            }
        }else if(cover_type === 'both'){
            if((hq + sq) < 10){
                alert('부수 수량을 입력하세요(최소10부)');
                frm.hard_cover_quantity.focus();
                return false;
            }
            if((hard_color === '' && hard_gs === '') || soft_color === ''){
                alert('하드커버, 소프트커버 표지옵션을 선택하세요');
                return false;
            }
        }
    }
    if(frm.content_color.value === ''){
        alert('용지색상을 선택하세요');
        return false;
    }
    if(frm.bnw_page.value === '' && frm.color_page.value === ''){
        alert('본문 페이지수를 입력하세요');
        frm.bnw_page.focus();
        return false;
    }

    if(frm.color_page.value !== '' && frm.color_page.value !== '0'){
        if(frm.add_color_page.value === ''){
            alert('칼라페이지가 들어갈 쪽 번호를 입력하세요');
            frm.add_color_page.focus();
            return false;
        }
    }
    if(frm.university.value === ''){
        alert('학교명을 입력하세요');
        frm.university.focus();
        return false;
    }
    if(frm.major.value === ''){
        alert('학과명을 입력하세요');
        frm.major.focus();
        return false;
    }
    if(frm.cmd.value === ''){
        alert('시스템 에러');
        return false;
    }
    return true;
}


function copy_email()
{
    const email = "printniz@naver.com";
    const textarea = document.createElement('textarea');
    textarea.value = email;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    alert('이메일이 클립보드에 복사되었습니다.');
}