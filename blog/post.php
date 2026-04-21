<?php
/**
 * Individual Blog Post Page
 * Accessed via /blog/[slug] (rewritten by .htaccess to /blog/post.php?slug=...)
 */
require_once __DIR__ . '/../api/db-config.php';
require_once __DIR__ . '/../api/admin-auth.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: /blog/'); exit; }

$pdo = getDBConnection();
$now = date('Y-m-d H:i:s');
$isAdmin = isAdminLoggedIn();

// Fetch blog
$sql = "SELECT b.*, a.name as author_name, a.bio as author_bio, a.image as author_image, a.page_url as author_page
        FROM blogs b LEFT JOIN blog_authors a ON b.author_id = a.id
        WHERE b.slug = ? AND b.status != 'deleted'";
if (!$isAdmin) $sql .= " AND b.status = 'published' AND (b.publish_date IS NULL OR b.publish_date <= '" . $now . "')";
$stmt = $pdo->prepare($sql);
$stmt->execute([$slug]);
$blog = $stmt->fetch();

if (!$blog) {
    http_response_code(404);
    include __DIR__ . '/../404.html'; // fallback
    exit;
}

// Increment views (non-admin)
if (!$isAdmin) {
    // Rate limit by session to avoid repeat counting
    if (session_status() === PHP_SESSION_NONE) session_start();
    $viewKey = 'viewed_blog_'.$blog['id'];
    if (empty($_SESSION[$viewKey])) {
        $pdo->prepare('UPDATE blogs SET views = views + 1 WHERE id = ?')->execute([$blog['id']]);
        $_SESSION[$viewKey] = true;
    }
}

// Fetch tags
$tagStmt = $pdo->prepare('SELECT t.name, t.slug FROM blog_tags t JOIN blog_tag_relations r ON t.id = r.tag_id WHERE r.blog_id = ?');
$tagStmt->execute([$blog['id']]);
$tags = $tagStmt->fetchAll();

// Fetch categories
$catIds = json_decode($blog['category_ids'] ?? '[]', true);
$allCats = [];
if ($catIds) {
    $pl = implode(',', array_fill(0, count($catIds), '?'));
    $cs = $pdo->prepare("SELECT id, name, slug FROM blog_categories WHERE id IN ($pl)");
    $cs->execute($catIds);
    $allCats = $cs->fetchAll();
}

// TOC
$toc = json_decode($blog['toc'] ?? '[]', true);

// Related posts (same category, different post)
$relatedPosts = [];
if ($catIds) {
    $catId = $catIds[0];
    $relStmt = $pdo->prepare("SELECT b.id, b.title, b.slug, b.excerpt, b.feature_image, b.feature_image_alt, b.publish_date, b.read_time, a.name as author_name
        FROM blogs b LEFT JOIN blog_authors a ON b.author_id = a.id
        WHERE b.status = 'published' AND b.id != ? AND JSON_CONTAINS(b.category_ids, ?)
        AND (b.publish_date IS NULL OR b.publish_date <= ?) ORDER BY RAND() LIMIT 3");
    $relStmt->execute([$blog['id'], (string)$catId, $now]);
    $relatedPosts = $relStmt->fetchAll();
}

// SEO preparation
$siteUrl    = 'https://www.chandigarhuniversity.online';
$metaTitle  = $blog['meta_title'] ?: $blog['title'];
$metaDesc   = $blog['meta_description'] ?: $blog['excerpt'];
$ogImage    = $blog['og_image'] ?: $blog['feature_image'];
$canonical  = $blog['canonical_url'] ?: $siteUrl.'/blog/'.$blog['slug'];
$pubDate    = $blog['publish_date'] ? date('Y-m-d', strtotime($blog['publish_date'])) : date('Y-m-d', strtotime($blog['created_at']));
$updDate    = date('Y-m-d', strtotime($blog['updated_at']));
$readTime   = $blog['read_time'] ?? 5;

// Programs for lead form
$programs = ['Online MBA','Online BBA','Online MCA','Online BCA','Online MA JMC','Online BA JMC','MSc Data Science','MA English','MA Economics','MSc Mathematics'];

// Build FAQ schema from content
$faqSchema = null;
preg_match_all('/<h3 class="faq-question">(.*?)<\/h3>\s*<div class="faq-answer">(.*?)<\/div>/si', $blog['content'], $faqMatches, PREG_SET_ORDER);
if ($faqMatches) {
    $faqItems = [];
    foreach ($faqMatches as $m) {
        $faqItems[] = [
            '@type'          => 'Question',
            'name'           => strip_tags($m[1]),
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($m[2])]
        ];
    }
    $faqSchema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqItems];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($metaTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
<?php if ($blog['focus_keyword']): ?><meta name="keywords" content="<?= htmlspecialchars($blog['focus_keyword']) ?>"><?php endif; ?>
<meta name="author" content="<?= htmlspecialchars($blog['author_name'] ?? 'Chandigarh University Online') ?>">
<meta name="robots" content="INDEX, FOLLOW">
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
<!-- Open Graph -->
<meta property="og:type" content="article">
<meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:site_name" content="Chandigarh University Online">
<?php if ($ogImage): ?><meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>
<meta property="article:published_time" content="<?= $pubDate ?>">
<meta property="article:modified_time" content="<?= $updDate ?>">
<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($metaTitle) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($metaDesc) ?>">
<?php if ($ogImage): ?><meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>

<!-- Schema: BlogPosting -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": <?= json_encode($blog['title']) ?>,
    "description": <?= json_encode($metaDesc) ?>,
    "url": <?= json_encode($canonical) ?>,
    "datePublished": "<?= $pubDate ?>",
    "dateModified": "<?= $updDate ?>",
    <?php if ($ogImage): ?>"image": <?= json_encode($ogImage) ?>,<?php endif; ?>
    "author": {
        "@type": "Person",
        "name": <?= json_encode($blog['author_name'] ?? 'CU Online Team') ?>
    },
    "publisher": {
        "@type": "Organization",
        "name": "Chandigarh University Online",
        "logo": { "@type": "ImageObject", "url": "<?= $siteUrl ?>/assets/images/cu-logo.webp" }
    },
    "wordCount": <?= str_word_count(strip_tags($blog['content'])) ?>,
    "timeRequired": "PT<?= $readTime ?>M"
}
</script>

<!-- Schema: Breadcrumb -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type":"ListItem","position":1,"name":"Home","item":"<?= $siteUrl ?>"},
        {"@type":"ListItem","position":2,"name":"Blog","item":"<?= $siteUrl ?>/blog/"},
        {"@type":"ListItem","position":3,"name":<?= json_encode($blog['title']) ?>,"item":<?= json_encode($canonical) ?>}
    ]
}
</script>
<?php if ($faqSchema): ?>
<script type="application/ld+json"><?= json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

<link rel="shortcut icon" href="/assets/uploads/favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/bootstrap.min-original.css">
<link rel="stylesheet" href="/assets/css/header-footer.css">
<link rel="stylesheet" href="/assets/css/plugins.css">
<link rel="stylesheet" href="/assets/css/custom-style.css">
<link rel="stylesheet" href="/assets/css/blog-content.css">
<style>
@media(min-width:991px){.cu-logo{height:85px;width:auto}.header-upper .hts-text{display:none}.header-upper .fa-whatsapp{display:none}.header-upper .ph-missed{display:none}}
.aws-logo{width:87px;height:25px;margin-left:3px}
.need-help p{font-size:20px;color:var(--bs-white);font-weight:700;font-family:'GoogleSans';margin-top:-5px;margin-bottom:0;text-decoration:none}
.dropdownHeading{font-size:13px!important}
.main-header-new .navbar .nav-link{font-size:14px;padding-left:8px;padding-right:8px}
@media(min-width:991px){.main-header-new .navbar .nav-link{white-space:nowrap}}
</style>
<style>
body{font-family:'Poppins',sans-serif;color:#374151}
.post-hero{background:linear-gradient(160deg,#0f1d35 0%,#1a2d4a 100%);padding:48px 0 0;color:#fff;position:relative;overflow:hidden}
.post-hero::after{content:'';position:absolute;bottom:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#d42b2b,#ff6b6b)}
.breadcrumb-bar{font-size:13px;color:rgba(255,255,255,0.6);margin-bottom:20px}
.breadcrumb-bar a{color:rgba(255,255,255,0.7);text-decoration:none}
.breadcrumb-bar a:hover{color:#fff}
.breadcrumb-bar span{color:rgba(255,255,255,0.4);margin:0 8px}
.post-cats{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.post-cat-badge{background:rgba(212,43,43,0.25);border:1px solid rgba(212,43,43,0.4);color:#fca5a5;padding:4px 14px;border-radius:50px;font-size:12px;font-weight:600;text-decoration:none}
.post-cat-badge:hover{background:rgba(212,43,43,0.4);color:#fff}
.post-h1{font-size:36px;font-weight:800;color:#fff;line-height:1.25;margin:0 0 20px;letter-spacing:-0.5px}
.post-meta-row{display:flex;align-items:center;gap:20px;flex-wrap:wrap;padding:20px 0}
.post-meta-item{display:flex;align-items:center;gap:6px;font-size:13px;color:rgba(255,255,255,0.65)}
.post-meta-item i{font-size:12px}
.author-chip{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.1);padding:6px 14px;border-radius:50px}
.author-chip img{width:24px;height:24px;border-radius:50%;object-fit:cover}
.author-chip-initial{width:24px;height:24px;border-radius:50%;background:#d42b2b;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}

.feature-img-wrap{max-width:1200px;margin:20px auto 0;padding:0 20px;position:relative;z-index:2}
.feature-img-wrap img{width:100%;max-height:500px;object-fit:cover;border-radius:16px;box-shadow:0 16px 48px rgba(0,0,0,0.3)}

.post-layout{display:grid;grid-template-columns:1fr 300px;gap:32px;align-items:start;padding:40px 0}
@media(max-width:960px){.post-layout{grid-template-columns:1fr}.post-aside{display:none}}
@media(max-width:640px){.post-h1{font-size:26px}.feature-img-wrap img{border-radius:10px}}

/* TOC sticky sidebar */
.toc-sticky{position:sticky;top:20px}
.admin-preview-bar{background:#d42b2b;color:#fff;padding:8px 20px;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:space-between}
.admin-preview-bar a{color:#fff;text-decoration:none;background:rgba(255,255,255,0.2);padding:4px 12px;border-radius:6px;font-size:12px}

.author-box{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:24px;display:flex;gap:20px;align-items:flex-start;margin-top:40px;box-shadow:0 2px 12px rgba(0,0,0,0.06)}
.author-box-img{width:72px;height:72px;border-radius:50%;object-fit:cover;flex-shrink:0;border:3px solid #f1f5f9}
.author-box-initial{width:72px;height:72px;border-radius:50%;background:#0f1d35;color:#fff;font-size:24px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.author-box-name{font-size:16px;font-weight:700;color:#0f1d35;margin:0 0 6px}
.author-box-bio{font-size:13.5px;color:#6b7280;margin:0;line-height:1.6}
.author-box-link{font-size:13px;color:#d42b2b;text-decoration:none;margin-top:8px;display:inline-block}

.tag-cloud{display:flex;flex-wrap:wrap;gap:8px;margin-top:20px}
.tag-badge{background:#f1f5f9;color:#6b7280;padding:5px 14px;border-radius:50px;font-size:12px;font-weight:600;text-decoration:none;transition:all 0.2s}
.tag-badge:hover{background:#0f1d35;color:#fff}
.tag-badge::before{content:'#'}

.social-share{display:flex;gap:10px;flex-wrap:wrap;margin:24px 0}
.share-btn{padding:9px 16px;border-radius:8px;border:none;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;text-decoration:none;transition:all 0.2s;font-family:'Poppins',sans-serif}
.share-wa{background:#25D366;color:#fff}.share-wa:hover{background:#1da851}
.share-fb{background:#1877F2;color:#fff}.share-fb:hover{background:#1560cc}
.share-tw{background:#1DA1F2;color:#fff}.share-tw:hover{background:#1a8fd1}
.share-li{background:#0A66C2;color:#fff}.share-li:hover{background:#084e9c}
.share-copy{background:#f1f5f9;color:#374151;border:1.5px solid #e5e7eb}.share-copy:hover{background:#e5e7eb}

.related-section{padding:40px 0;background:#f8fafc;margin-top:40px}
.related-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
@media(max-width:768px){.related-grid{grid-template-columns:1fr}}
.related-card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.07);transition:transform 0.2s}
.related-card:hover{transform:translateY(-3px)}
.related-card img{width:100%;height:160px;object-fit:cover}
.related-card-body{padding:16px}
.related-card-title{font-size:14px;font-weight:700;color:#0f1d35;margin:0 0 6px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.related-card-title a{color:inherit;text-decoration:none}
.related-card-title a:hover{color:#d42b2b}
.related-card-meta{font-size:12px;color:#9ca3af}

.toc-mobile-toggle{display:none;background:#f1f5f9;border:none;border-radius:8px;padding:10px 16px;font-size:14px;font-weight:600;cursor:pointer;width:100%;text-align:left;font-family:'Poppins',sans-serif;color:#0f1d35;margin-bottom:16px}
@media(max-width:960px){.toc-mobile-toggle{display:flex;align-items:center;justify-content:space-between}.toc-box{display:none}.toc-box.toc-open{display:block}}
/* Fixed header offset */
body{padding-top:165px}
@media(max-width:991px){body{padding-top:70px}}
</style>
</head>
<body>

<?php if ($isAdmin): ?>
<div class="admin-preview-bar">
    <span><i class="fas fa-eye"></i> Admin Preview Mode</span>
    <a href="/admin/blog-editor.php?id=<?= $blog['id'] ?>"><i class="fas fa-edit"></i> Edit This Post</a>
</div>
<?php endif; ?>

<!-- Site header -->
<?php include_once __DIR__ . '/../includes/site-header.php'; ?>

<!-- Hero -->
<section class="post-hero">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px">
        <div class="breadcrumb-bar">
            <a href="/">Home</a><span>›</span>
            <a href="/blog/">Blog</a><span>›</span>
            <?php if ($allCats): ?><a href="/blog/?category=<?= htmlspecialchars($allCats[0]['slug']) ?>"><?= htmlspecialchars($allCats[0]['name']) ?></a><span>›</span><?php endif; ?>
            <span style="color:rgba(255,255,255,0.5)"><?= htmlspecialchars(mb_strimwidth($blog['title'], 0, 50, '...')) ?></span>
        </div>

        <?php if ($allCats): ?>
        <div class="post-cats">
            <?php foreach ($allCats as $c): ?>
            <a href="/blog/?category=<?= htmlspecialchars($c['slug']) ?>" class="post-cat-badge"><?= htmlspecialchars($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <h1 class="post-h1"><?= htmlspecialchars($blog['title']) ?></h1>

        <div class="post-meta-row">
            <?php if ($blog['author_name']): ?>
            <div class="post-meta-item author-chip">
                <?php if ($blog['author_image']): ?>
                <img src="<?= htmlspecialchars($blog['author_image']) ?>" alt="<?= htmlspecialchars($blog['author_name']) ?>">
                <?php else: ?>
                <div class="author-chip-initial"><?= strtoupper(substr($blog['author_name'], 0, 1)) ?></div>
                <?php endif; ?>
                <span style="font-weight:600;color:#fff"><?= htmlspecialchars($blog['author_name']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($blog['publish_date']): ?>
            <div class="post-meta-item"><i class="fas fa-calendar-alt"></i> <?= date('d F Y', strtotime($blog['publish_date'])) ?></div>
            <?php endif; ?>
            <?php if ($readTime): ?>
            <div class="post-meta-item"><i class="fas fa-clock"></i> <?= $readTime ?> min read</div>
            <?php endif; ?>
            <div class="post-meta-item"><i class="fas fa-eye"></i> <?= number_format($blog['views']) ?> views</div>
        </div>
    </div>
</section>

<!-- Feature Image -->
<?php if ($blog['feature_image']): ?>
<div style="background:#0f1d35;padding-bottom:32px">
    <div class="feature-img-wrap">
        <img src="<?= htmlspecialchars($blog['feature_image']) ?>"
             alt="<?= htmlspecialchars($blog['feature_image_alt'] ?? $blog['title']) ?>"
             title="<?= htmlspecialchars($blog['feature_image_title'] ?? '') ?>"
             width="1200" height="628">
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<div style="max-width:1200px;margin:0 auto;padding:0 20px">
<div class="post-layout">

    <!-- Content Column -->
    <main>
        <!-- Mobile TOC Toggle -->
        <?php if ($toc): ?>
        <button class="toc-mobile-toggle" onclick="this.nextElementSibling.classList.toggle('toc-open');this.querySelector('.toc-toggle-icon').classList.toggle('fa-chevron-up');this.querySelector('.toc-toggle-icon').classList.toggle('fa-chevron-down')">
            <span><i class="fas fa-list-ol" style="color:#d42b2b;margin-right:8px"></i>Table of Contents</span>
            <i class="fas fa-chevron-down toc-toggle-icon"></i>
        </button>
        <div class="toc-box">
            <ul class="toc-list">
            <?php foreach ($toc as $item): ?>
            <li class="toc-<?= $item['tag'] ?>">
                <a href="#<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['text']) ?></a>
            </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Blog Content -->
        <div class="blog-post-content">
            <?= $blog['content'] ?>
        </div>

        <!-- Tags -->
        <?php if ($tags): ?>
        <div class="tag-cloud">
            <?php foreach ($tags as $tag): ?>
            <a href="/blog/?tag=<?= urlencode($tag['slug']) ?>" class="tag-badge"><?= htmlspecialchars($tag['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Social Share -->
        <div>
            <div style="font-size:14px;font-weight:700;color:#0f1d35;margin-bottom:12px"><i class="fas fa-share-alt" style="color:#d42b2b;margin-right:6px"></i>Share this article</div>
            <div class="social-share">
                <a href="https://wa.me/?text=<?= urlencode($blog['title'].' '.$canonical) ?>" target="_blank" class="share-btn share-wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonical) ?>" target="_blank" class="share-btn share-fb"><i class="fab fa-facebook-f"></i> Facebook</a>
                <a href="https://twitter.com/intent/tweet?text=<?= urlencode($blog['title']) ?>&url=<?= urlencode($canonical) ?>" target="_blank" class="share-btn share-tw"><i class="fab fa-twitter"></i> Twitter</a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($canonical) ?>&title=<?= urlencode($blog['title']) ?>" target="_blank" class="share-btn share-li"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
                <button onclick="copyLink()" class="share-btn share-copy"><i class="fas fa-link"></i> Copy Link</button>
            </div>
        </div>

        <!-- Author Box -->
        <?php if ($blog['author_name']): ?>
        <div class="author-box">
            <?php if ($blog['author_image']): ?>
            <img src="<?= htmlspecialchars($blog['author_image']) ?>" alt="<?= htmlspecialchars($blog['author_name']) ?>" class="author-box-img">
            <?php else: ?>
            <div class="author-box-initial"><?= strtoupper(substr($blog['author_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9ca3af;letter-spacing:1px;margin-bottom:4px">Author</div>
                <div class="author-box-name"><?= htmlspecialchars($blog['author_name']) ?></div>
                <?php if ($blog['author_bio']): ?>
                <p class="author-box-bio"><?= htmlspecialchars($blog['author_bio']) ?></p>
                <?php endif; ?>
                <?php if ($blog['author_page']): ?>
                <a href="<?= htmlspecialchars($blog['author_page']) ?>" target="_blank" class="author-box-link">View Profile <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Sidebar -->
    <aside class="post-aside">
        <div class="toc-sticky">
            <?php if ($toc): ?>
            <div class="toc-box sidebar-widget" style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,0.07);margin-bottom:20px">
                <div class="toc-title" style="font-size:14px;font-weight:700;color:#0f1d35;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                    <i class="fas fa-list-ol" style="color:#d42b2b"></i> Table of Contents
                </div>
                <ul class="toc-list">
                <?php foreach ($toc as $item): ?>
                <li class="toc-<?= $item['tag'] ?>">
                    <a href="#<?= htmlspecialchars($item['id']) ?>"><?= htmlspecialchars($item['text']) ?></a>
                </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- CTA Widget -->
            <div style="background:linear-gradient(135deg,#0f1d35,#1a2d4a);border-radius:16px;padding:24px;box-shadow:0 4px 20px rgba(15,29,53,0.25)">
                <div style="font-size:13px;font-weight:700;color:#fca5a5;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px">Ready to Study Online?</div>
                <h3 style="color:#fff;font-size:18px;font-weight:800;margin:0 0 12px;line-height:1.3">Talk to an Admissions Counselor</h3>
                <p style="font-size:13px;color:rgba(255,255,255,0.7);margin:0 0 18px;line-height:1.6">Get personalized guidance for program selection, fees & admission process.</p>
                <form id="sidebarLeadForm" action="/api/submit-lead.php" method="POST">
                    <input type="text" name="StudentName" placeholder="Your Name *" required style="width:100%;padding:10px 14px;border-radius:8px;border:none;font-size:13px;margin-bottom:10px;font-family:'Poppins',sans-serif">
                    <input type="email" name="StudentEmail" placeholder="Email Address *" required style="width:100%;padding:10px 14px;border-radius:8px;border:none;font-size:13px;margin-bottom:10px;font-family:'Poppins',sans-serif">
                    <input type="tel" name="StudentMobile" placeholder="Phone Number *" required style="width:100%;padding:10px 14px;border-radius:8px;border:none;font-size:13px;margin-bottom:10px;font-family:'Poppins',sans-serif">
                    <select name="StudentProgram" required style="width:100%;padding:10px 14px;border-radius:8px;border:none;font-size:13px;margin-bottom:14px;font-family:'Poppins',sans-serif">
                        <option value="">Select Program *</option>
                        <?php foreach ($programs as $p): ?><option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option><?php endforeach; ?>
                    </select>
                    <input type="hidden" name="source" value="Blog Sidebar Form">
                    <button type="submit" style="width:100%;padding:12px;background:#d42b2b;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif">
                        Get Free Counseling <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

</div>
</div>

<!-- Related Posts -->
<?php if ($relatedPosts): ?>
<section class="related-section">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px">
        <h2 style="font-size:24px;font-weight:800;color:#0f1d35;margin:0 0 24px">Related Articles</h2>
        <div class="related-grid">
        <?php foreach ($relatedPosts as $rp): ?>
        <article class="related-card">
            <?php if ($rp['feature_image']): ?>
            <img src="<?= htmlspecialchars($rp['feature_image']) ?>" alt="<?= htmlspecialchars($rp['feature_image_alt'] ?? '') ?>" loading="lazy">
            <?php else: ?>
            <div style="width:100%;height:160px;background:linear-gradient(135deg,#0f1d35,#1a2d4a);display:flex;align-items:center;justify-content:center"><i class="fas fa-newspaper" style="color:rgba(255,255,255,0.3);font-size:32px"></i></div>
            <?php endif; ?>
            <div class="related-card-body">
                <h3 class="related-card-title"><a href="/blog/<?= htmlspecialchars($rp['slug']) ?>"><?= htmlspecialchars($rp['title']) ?></a></h3>
                <div class="related-card-meta">
                    <?php if ($rp['read_time']): ?><i class="fas fa-clock"></i> <?= $rp['read_time'] ?> min read<?php endif; ?>
                    <?php if ($rp['publish_date']): ?> · <?= date('d M Y', strtotime($rp['publish_date'])) ?><?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Site Footer -->
<?php include_once __DIR__ . '/../includes/site-footer.php'; ?>

<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/plugins.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Smooth scroll for TOC links
    document.querySelectorAll('.toc-list a, .toc-box a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Highlight active TOC item on scroll
    var headings = document.querySelectorAll('.blog-post-content h2, .blog-post-content h3, .blog-post-content h4');
    var tocLinks = document.querySelectorAll('.toc-list a');
    window.addEventListener('scroll', function() {
        var current = '';
        headings.forEach(function(h) { if (h.getBoundingClientRect().top < 100) current = '#' + h.id; });
        tocLinks.forEach(function(a) {
            a.style.color = '';
            a.style.fontWeight = '';
            if (a.getAttribute('href') === current) {
                a.style.color = '#d42b2b';
                a.style.fontWeight = '700';
            }
        });
    }, { passive: true });

    // Handle all embedded .blf-form lead forms in blog content
    document.querySelectorAll('.blf-form').forEach(function(form) {
        form.removeAttribute('onsubmit');
        form.addEventListener('submit', function(e) {
            handleBlogLeadForm(form, e);
        });
    });

    // Sidebar lead form — submit via fetch, no page redirect
    var leadForm = document.getElementById('sidebarLeadForm');
    if (leadForm) {
        leadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = leadForm.querySelector('button[type="submit"]');
            var origHTML = btn ? btn.innerHTML : '';
            if (btn) { btn.innerHTML = 'Sending...'; btn.disabled = true; }
            fetch('/api/submit-lead.php', { method: 'POST', body: new FormData(leadForm) })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.Status === 'Success' || res.status === 'success') {
                    leadForm.innerHTML = '<div style="text-align:center;padding:20px"><i class="fas fa-check-circle" style="font-size:40px;color:#10b981;display:block;margin-bottom:10px"></i><strong style="color:#fff;font-size:16px">Thank you!</strong><p style="color:rgba(255,255,255,0.8);font-size:14px;margin-top:4px">Our team will contact you shortly.</p></div>';
                } else {
                    if (btn) { btn.innerHTML = origHTML; btn.disabled = false; }
                    alert(res.Message || 'Please fill all required fields.');
                }
            })
            .catch(function() {
                if (btn) { btn.innerHTML = origHTML; btn.disabled = false; }
                alert('Connection error. Please try again.');
            });
        });
    }

});

// Global handler for lead forms embedded in blog content via onsubmit
function handleBlogLeadForm(form, e) {
    e.preventDefault();
    var btn = form.querySelector('button[type="submit"]');
    var origHTML = btn ? btn.innerHTML : '';
    if (btn) { btn.innerHTML = 'Sending...'; btn.disabled = true; }
    fetch('/api/submit-lead.php', { method: 'POST', body: new FormData(form) })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.Status === 'Success' || res.status === 'success') {
            form.innerHTML = '<div style="text-align:center;padding:20px;color:#fff"><i class="fas fa-check-circle" style="font-size:36px;color:#10b981;margin-bottom:8px;display:block"></i><strong>Thank you!</strong> We\'ll call you shortly.</div>';
        } else {
            if (btn) { btn.innerHTML = origHTML; btn.disabled = false; }
            alert(res.Message || 'Please fill all required fields.');
        }
    })
    .catch(function() {
        if (btn) { btn.innerHTML = origHTML; btn.disabled = false; }
        alert('Connection error. Please try again.');
    });
    return false;
}

// Copy link (needs to be global for onclick attribute)
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        var btn = document.querySelector('.share-copy');
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.style.background = '#10b981';
        btn.style.color = '#fff';
        setTimeout(function() { btn.innerHTML = orig; btn.style.background = ''; btn.style.color = ''; }, 2500);
    });
}
</script>
</body>
</html>
