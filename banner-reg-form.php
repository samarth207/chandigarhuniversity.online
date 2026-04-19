<?php
$source = htmlspecialchars($_GET['source'] ?? 'Website', ENT_QUOTES, 'UTF-8');
$media = htmlspecialchars($_GET['media'] ?? '', ENT_QUOTES, 'UTF-8');
$campaign = htmlspecialchars($_GET['campaign'] ?? '', ENT_QUOTES, 'UTF-8');
$basePath = htmlspecialchars($_GET['basePath'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<style>
.banner-reg-form input, .banner-reg-form select { width: 100%; padding: 8px 10px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; box-sizing: border-box; }
.banner-reg-form .btn-submit { width: 100%; padding: 10px; background-color: #e0181e; color: #fff; border: none; border-radius: 4px; font-size: 15px; font-weight: 600; cursor: pointer; }
.banner-reg-form .btn-submit:hover { background-color: #c01518; }
.banner-reg-form .phone-group { display: flex; gap: 6px; }
.banner-reg-form .phone-group select { width: 70px; flex-shrink: 0; }
.banner-reg-form .phone-group input { flex: 1; }
.banner-reg-form .msg { text-align: center; padding: 6px; margin-bottom: 8px; border-radius: 4px; display: none; font-size: 13px; }
.banner-reg-form .msg.error { background: #ffe0e0; color: #c00; display: block; }
.banner-reg-form .msg.success { background: #e0ffe0; color: #060; display: block; }
</style>
<div class="banner-reg-form">
    <div id="bannerFormMsg" class="msg"></div>
    <form id="bannerRegForm" onsubmit="return submitBannerForm(event)">
        <input type="text" name="name" placeholder="Full Name *" required minlength="2" maxlength="100">
        <input type="email" name="email" placeholder="Email *" required>
        <div class="phone-group">
            <select name="countryCode"><option value="+91" selected>+91</option></select>
            <input type="tel" name="mobile" placeholder="Mobile *" required pattern="[0-9]{10}" maxlength="10">
        </div>
        <select name="program" required>
            <option value="">Select Program *</option>
        </select>
        <input type="hidden" name="source" value="<?php echo $source; ?>">
        <button type="submit" class="btn-submit">Apply Now</button>
    </form>
</div>
<script>
(function(){
    var form = document.getElementById('bannerRegForm');
    var sel = form.querySelector('select[name="program"]');
    fetch('<?php echo $basePath; ?>api/get-programs.php')
        .then(function(r){return r.json()})
        .then(function(data){
            data.forEach(function(item){
                var o = document.createElement('option');
                o.value = item.Code;
                o.textContent = item.ShortName;
                sel.appendChild(o);
            });
        }).catch(function(){});
})();
function submitBannerForm(e){
    e.preventDefault();
    var f = document.getElementById('bannerRegForm');
    var msg = document.getElementById('bannerFormMsg');
    msg.className = 'msg'; msg.style.display = 'none';
    var btn = f.querySelector('.btn-submit');
    btn.disabled = true; btn.textContent = 'Submitting...';
    var data = {
        StudentName: f.name.value,
        StudentEmail: f.email.value,
        StudentMobile: f.mobile.value,
        StudentProgram: f.program.value,
        StudentSource: f.source.value || 'Website',
        StudentCountryCode: f.countryCode.value,
        mx_Param1: '', mx_Param2: '', mx_Param3: ''
    };
    var basePath = '<?php echo $basePath; ?>';
    fetch(basePath + 'api/submit-lead.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }).then(function(r){return r.json()}).then(function(res){
        if(res.Status === 'Success'){
            msg.className = 'msg success'; msg.textContent = 'Application submitted!'; msg.style.display = 'block';
            f.reset();
            setTimeout(function(){ window.location.href = basePath + 'thanks.html'; }, 1500);
        } else {
            msg.className = 'msg error'; msg.textContent = res.Message || 'Submission failed.'; msg.style.display = 'block';
        }
        btn.disabled = false; btn.textContent = 'Apply Now';
    }).catch(function(){
        msg.className = 'msg error'; msg.textContent = 'Network error. Please try again.'; msg.style.display = 'block';
        btn.disabled = false; btn.textContent = 'Apply Now';
    });
    return false;
}
</script>
