<?php
$source = htmlspecialchars($_GET['source'] ?? 'Website', ENT_QUOTES, 'UTF-8');
$media = htmlspecialchars($_GET['media'] ?? '', ENT_QUOTES, 'UTF-8');
$campaign = htmlspecialchars($_GET['campaign'] ?? '', ENT_QUOTES, 'UTF-8');
$course = htmlspecialchars($_GET['course'] ?? '', ENT_QUOTES, 'UTF-8');
$brochureCourse = htmlspecialchars($_GET['brochure-course'] ?? '', ENT_QUOTES, 'UTF-8');
$basePath = htmlspecialchars($_GET['basePath'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<style>
.brochure-form input, .brochure-form select { width: 100%; padding: 8px 10px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; box-sizing: border-box; }
.brochure-form .btn-submit { width: 100%; padding: 10px; background-color: #e0181e; color: #fff; border: none; border-radius: 4px; font-size: 15px; font-weight: 600; cursor: pointer; }
.brochure-form .btn-submit:hover { background-color: #c01518; }
.brochure-form .phone-group { display: flex; gap: 6px; }
.brochure-form .phone-group select { width: 70px; flex-shrink: 0; }
.brochure-form .phone-group input { flex: 1; }
.brochure-form .msg { text-align: center; padding: 6px; margin-bottom: 8px; border-radius: 4px; display: none; font-size: 13px; }
.brochure-form .msg.error { background: #ffe0e0; color: #c00; display: block; }
.brochure-form .msg.success { background: #e0ffe0; color: #060; display: block; }
</style>
<div class="brochure-form">
    <p style="font-size:16px; font-weight:700; margin-bottom:10px; text-align:center;">Download Brochure</p>
    <div id="brochureFormMsg" class="msg"></div>
    <form id="brochureForm" onsubmit="return submitBrochureForm(event)">
        <input type="text" name="name" placeholder="Full Name *" required minlength="2" maxlength="100">
        <input type="email" name="email" placeholder="Email *" required>
        <div class="phone-group">
            <select name="countryCode"><option value="+91" selected>+91</option></select>
            <input type="tel" name="mobile" placeholder="Mobile *" required pattern="[0-9]{10}" maxlength="10">
        </div>
        <input type="hidden" name="source" value="<?php echo $source; ?>">
        <input type="hidden" name="course" value="<?php echo $course; ?>">
        <input type="hidden" name="brochureCourse" value="<?php echo $brochureCourse; ?>">
        <button type="submit" class="btn-submit">Download Brochure</button>
    </form>
</div>
<script>
function submitBrochureForm(e){
    e.preventDefault();
    var f = document.getElementById('brochureForm');
    var msg = document.getElementById('brochureFormMsg');
    msg.className = 'msg'; msg.style.display = 'none';
    var btn = f.querySelector('.btn-submit');
    btn.disabled = true; btn.textContent = 'Submitting...';
    var data = {
        StudentName: f.name.value,
        StudentEmail: f.email.value,
        StudentMobile: f.mobile.value,
        StudentProgram: f.course.value || 'Brochure',
        StudentSource: f.source.value || 'Brochure Download',
        StudentCountryCode: f.countryCode.value,
        mx_Param1: 'brochure', mx_Param2: f.brochureCourse.value || '', mx_Param3: ''
    };
    var basePath = '<?php echo $basePath; ?>';
    fetch(basePath + 'api/submit-lead.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }).then(function(r){return r.json()}).then(function(res){
        if(res.Status === 'Success'){
            msg.className = 'msg success'; msg.textContent = 'Brochure will download shortly!'; msg.style.display = 'block';
            f.reset();
        } else {
            msg.className = 'msg error'; msg.textContent = res.Message || 'Submission failed.'; msg.style.display = 'block';
        }
        btn.disabled = false; btn.textContent = 'Download Brochure';
    }).catch(function(){
        msg.className = 'msg error'; msg.textContent = 'Network error. Please try again.'; msg.style.display = 'block';
        btn.disabled = false; btn.textContent = 'Download Brochure';
    });
    return false;
}
</script>
