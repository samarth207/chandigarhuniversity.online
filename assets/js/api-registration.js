function getCuBasePath() {
    var path = window.location.pathname;
    // Under /blog/cu/ (blog post pages) -> two levels up to site root so reg-form.php loads from talentedge-cu-website/
    if (path.indexOf('/blog/cu/') !== -1 || path.indexOf('/blog/cu') === path.length - 9) {
        return '../../';
    }
    // Under /blog/ (main blog index) -> one level up to site root so reg-form.php loads from site root
    if (path.indexOf('/blog/') !== -1 || path === '/blog' || path.lastIndexOf('/blog') === path.length - 5) {
        return '../';
    }
    // Under /us/ and also under a program path (e.g. /us/online-bba/hrm) -> two levels up to site root
    if (path.indexOf('/us/') !== -1 && (path.indexOf('/online-mba/') !== -1 || path.indexOf('/online-bba/') !== -1 || path.indexOf('/online-mca/') !== -1)) {
        return '../../';
    }
    // Under program path at root level (e.g. /online-mba/marketing) -> one level up
    if (path.indexOf('/online-mba/') !== -1 || path.indexOf('/online-bba/') !== -1 || path.indexOf('/online-mca/') !== -1) {
        return '../';
    }
    return '';
}

function LoadRegistrationForm(target, source, media, campaign, ltype="") {
    //setTimeout(function () {
        $('#' + target).html('<div style="text-align: center;"><p>Loading Registration Form...</p></div>');
        
        var basePath = getCuBasePath();
        var url = basePath + 'reg-form.php';
        var params = [];
        
        if (source) {
            params.push('source=' + encodeURIComponent(source));
        }
        if (media) {
            params.push('media=' + encodeURIComponent(media));
        }
        if (campaign) {
            params.push('campaign=' + encodeURIComponent(campaign));
        }
        if (ltype) {
            params.push('type=' + encodeURIComponent(ltype));
        }
        params.push('basePath=' + encodeURIComponent(basePath));
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        $.ajax({
            url: url,
            crossOrigin: true,
            success: function (data) {
                $('#' + target).html(data);
            }
        });
}

function LoadBannerRegistrationForm(target, source, media, campaign, ltype="") {
    $('#' + target).html('<div style="text-align: center; padding: 20px;"><p style="font-size: 13px; margin-top: 10px;">Loading Form...</p></div>');
    
    var basePath = getCuBasePath();
    var url = basePath + 'banner-reg-form.php';
    
    var params = [];
    
    if (source) {
        params.push('source=' + encodeURIComponent(source));
    }
    if (media) {
        params.push('media=' + encodeURIComponent(media));
    }
    if (campaign) {
        params.push('campaign=' + encodeURIComponent(campaign));
    }
    if (ltype) {
        params.push('type=' + encodeURIComponent(ltype));
    }
    params.push('basePath=' + encodeURIComponent(basePath));
    if (params.length > 0) {
        url += '?' + params.join('&');
    }
    
    $.ajax({
        url: url,
        crossOrigin: true,
        success: function (data) {
            $('#' + target).html(data);
        },
        error: function(xhr, status, error) {
            console.error('Error loading banner form:', error);
            $('#' + target).html('<p style="color: red; text-align: center;">Error loading form. Please refresh the page.</p>');
        }
    });
}

function LoadBrochureDownloadForm(target, source, media, campaign, ltype="", course="", brochure_course="") {
    $('#' + target).html('<div style="text-align: center; padding: 20px;"><p style="font-size: 13px; margin-top: 10px;">Loading Brochure Form...</p></div>');
    
    var basePath = getCuBasePath();
    var currentPath = window.location.pathname;
    var url = basePath + 'brochure-download-form.php';
    
    if (currentPath.includes('hp.php') || currentPath.endsWith('/hp')) {
        url = basePath + 'brochure-download-form-hp.php';
    }
    var params = [];
    
    if (source) {
        params.push('source=' + encodeURIComponent(source));
    }
    if (media) {
        params.push('media=' + encodeURIComponent(media));
    }
    if (campaign) {
        params.push('campaign=' + encodeURIComponent(campaign));
    }
    if (ltype) {
        params.push('type=' + encodeURIComponent(ltype));
    }
    if (course) {
        params.push('course=' + encodeURIComponent(course));
    }
    if (brochure_course) {
        params.push('brochure-course=' + encodeURIComponent(brochure_course));
    }
    params.push('basePath=' + encodeURIComponent(basePath));
    if (params.length > 0) {
        url += '?' + params.join('&');
    }

    $.ajax({
        url: url,
        crossOrigin: true,
        success: function (data) {
            $('#' + target).html(data);
        },
        error: function(xhr, status, error) {
            console.error('Error loading brochure form:', error);
            $('#' + target).html('<p style="color: red; text-align: center;">Error loading form. Please refresh the page.</p>');
        }
    });
}
function AutoCompleteTextBox(source, target, targetfull, servicepath, splitter, ref_fn) {
    try {


        $('#txtCity').autocomplete({

            source: function (request, response) {
                $.ajax({
                    url: servicepath,
                    crossOrigin: true,
                    dataType: 'json',
                    data: {
                        Prefix: request.term
                    },
                    success: function (data) {
                        response($.map(data, function (item) {
                            return {
                                //div: item.split('-')[0],
                                label: item.split('::')[0],
                                val: item.split('::')[1],
                                // value: item.split('-')[1]

                            }

                        }))
                    },
                    error: function (response) {
                        alert(response.responseText);
                    },
                    failure: function (response) {
                        alert(response.responseText);
                    }
                });
            },
            select: function (e, i) {
                if (target != '') {
                    $("#" + target).val(i.item.val);
                }
                if (targetfull != '') {
                    $("#" + targetfull).val(i.item.label);
                }
                if (ref_fn != '') {
                    window[ref_fn]($(this).attr('id'));
                }
            },
            minLength: 3
        });
    } catch (e) {

    }
}
function CheckEmailMobile(data, type, Source, target) {


    $('#' + target + '_Ok').attr('title', '');
    $.ajax({

        url: 'api/submit-lead.php?data=' + data + '&type=' + type + '&Source=direct',
        crossOrigin: true,
        success: function (msg) {
            $('#' + target).hide();

            $('#' + target + '_Ok').show();
            if (msg == 'ok') {
                if (type == 'e') {

                    setTimeout(function () {
                        if (!IsEmail(data)) {
                            $('#' + target + '_Ok').attr('title', 'Invalid');
                        }
                        else {
                            $('#' + target + '_Ok').attr('title', 'Valid');
                        }
                    }, 500);


                }
                else if (type == 'm') {
                    if (data.length == 10) {
                        $('#' + target + '_Ok').attr('title', 'Valid');
                    }
                    else {
                        $('#' + target + '_Ok').attr('title', 'Invalid');
                    }
                }


            }
            else {
                if (data != '') {
                    $('#p_Error').text(msg);
                    $('#p_Error').show();
                    $('#' + target + '_Ok').attr('title', msg);
                    $('#' + target + '_Ok').attr('title', 'Error');
                }
                else {
                    $('#p_Error').text('');
                    $('#p_Error').hide();
                    $('#' + target + '_Ok').attr('title', '');
                    $('#' + target + '_Ok').hide();
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
        }
    });
}
function IsEmail(email) {
    // Regular expression to validate email format
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function CheckTextValue(txtbox) {
    var remarks = '';
    for (var i = 0; i < txtbox.split(',').length; i++) {
        var data = txtbox.split(',')[i];
        var id = data.split(':')[0];
        var type = data.split(':')[1];
        if ($(type + '[id*="' + id + '"]').val() == '') {
            $(type + '[id*="' + id + '"]').addClass('Validation-Req');

            var template = '<div class="mktoError"><div class="mktoErrorArrowWrap"><div class="mktoErrorArrow"></div></div><div class="mktoErrorMsg">This field is required.</div></div>';
            if ($(type + '[id*="' + id + '"]').parent('div').html().indexOf('mktoError') == -1) {
                $(type + '[id*="' + id + '"]').parent('div').append(template);
            }
            if (remarks == '') {
                remarks = $(type + '[id*="' + id + '"]').attr('data-validation');
            }
            else {
                remarks = remarks + "," + $(type + '[id*="' + id + '"]').attr('data-validation');
            }

        }
        else {
            $(type + '[id*="' + id + '"]').removeClass('Validation-Req');

            $(type + '[id*="' + id + '"]').parent('div').find('div[class="mktoError"]').remove();
        }
    }
    return remarks;
}

function CheckDropValue(dropdown) {
    var remarks = '';
    for (var i = 0; i < dropdown.split(',').length; i++) {

        var id = dropdown.split(',')[i];
        if ($('select[id="' + id + '"]').val() == '0' || $('select[id="' + id + '"]').val() == '' || $('select[id="' + id + '"]').val() == 'undefined'
            || $('select[id="' + id + '"]').val() == 'null' || $('select[id="' + id + '"]').val() == 'Select' || $('select[id="' + id + '"]').val() == 'Select Discipline') {



            $('select[id="' + id + '"]').addClass('Validation-Req');
            if (remarks == '') {
                remarks = $('select[id="' + id + '"]').attr('data-validation');
            }
            else {
                remarks = remarks + "," + $('select[id="' + id + '"]').attr('data-validation');
            }

        }
        else {
            $('select[id="' + id + '"]').removeClass('Validation-Req');
        }
    }
    return remarks;
}

function CitySelected() {
    var CityIdFull = $('#hfCity').val();

    $('#hfCity').val(CityIdFull.split(':')[0]);
}
function CheckValidators() {
    setTimeout(function () {
        var Day = $('#ddlDate').val();
        var Month = $('#ddlMnt').val();
        var Year = $('#ddlyear').val();

        if (Day == '0' || Month == '0' || Year == '0') {
            //   $('.dob-inp').addClass('required-field');
            if (Day == '0') {
                $('#span_dob').text('Select Day');
            }
            else if (Month == '0') {
                $('#span_dob').text('Select Month');
            }
            else if (Year == '0') {
                $('#span_dob').text('Select Year');
            }
        }
        else {
            $('.dob-inp').removeClass('required-field');
            $('#span_dob').text('');
        }
    }, 200);

}

$(document).ready(function () {
    $('#btnSignIn').click(function (event) {
        event.preventDefault();
        SignIn();
    });
});
function LoadLoginPage(location) {
    var login_elem = document.createElement('div');
    login_elem.className = "userLoginFrom";
    login_elem.style = "display:none";
    $('body').append(login_elem);
    setTimeout(function () {
        $.ajax({
            url: '#',
            crossOrigin: true,
            success: function (data) {

                if (location.indexOf('aid.cuchd.in') > -1) {
                    data = data.replace('@logo', 'assets/images/cu-logo.webp');
                }
                else {
                    data = data.replace('@logo', 'assets/images/cu-logo.webp');
                }
                $('.userLoginFrom').html(data);

                setTimeout(function () {

                    $('.userLoginFrom').show();

                    $('#closePopup').on('click', function (e) {
                        $('.loginPopup').removeClass("swap");
                        $('body').removeClass("noscroll");
                        $('header').attr('style', 'z-index:99999');
                        e.preventDefault();
                    });


                    $('#btnSignIn').click(function () {
                        SignIn();
                    });

                    $("#txtPassword").keydown(function (e) {
                        if (e.keyCode == 13) {
                            SignIn();
                        }
                    });
                }, 500);
            }
        });
    }, 500);
}
function SignIn() {
    var username = $('#txtUserId').val();
    var password = $('#txtPassword').val();

    $(".login-fields-error").remove();

    if (username.length < 1) {
        $('#txtUserId').after('<span class="login-fields-error">Enter User Id</span>');
        document.getElementById("txtUserId").style.borderColor = "red";
    }
    else {
        document.getElementById("txtUserId").style.borderColor = "#ddd";
    }

    if (password.length < 1) {
        $('#txtPassword').after('<span class="login-fields-error">Enter Password</span>');
        document.getElementById("txtPassword").style.borderColor = "red";
    }
    else {
        document.getElementById("txtPassword").style.borderColor = "#ddd";
    }


    if (username != '' && password != '') {
        var api_url = 'api/submit-lead.php'

        $.get('api/submit-lead.php?UserId=' + username + '&Password=' + password, function (data) {

            var bj = jQuery.parseJSON(data);
            var url = bj.RedirectURL;

            var response = bj.Response;
            if (url != '') {
                window.location.href = url;
            }
            else {
                p_LoginError.innerText = response;
                alert('ERROR', 'User Id or Password not valid, please check and try again', 'error');
            }

        });
    }
}
