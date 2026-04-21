<?php
/**
 * Blog Listing Page — /blog/
 */
require_once __DIR__ . '/../api/db-config.php';

$pdo  = getDBConnection();
$now  = date('Y-m-d H:i:s');
$page = max(1, (int)($_GET['page'] ?? 1));
$cat  = trim($_GET['category'] ?? '');
$perPage = 9;
$offset  = ($page - 1) * $perPage;

// Category filter
$catInfo  = null;
$catWhere = '';
if ($cat) {
    $cStmt = $pdo->prepare('SELECT * FROM blog_categories WHERE slug = ? AND is_active = 1');
    $cStmt->execute([$cat]);
    $catInfo = $cStmt->fetch();
    if ($catInfo) {
        $catWhere = 'AND JSON_CONTAINS(b.category_ids, ?)';
    }
}

// Build params explicitly — no spread operator for PHP 7.4 compatibility
$baseWhere  = "b.status = 'published' AND (b.publish_date IS NULL OR b.publish_date <= ?) $catWhere";
$baseParams = $catInfo ? [$now, (string)(int)$catInfo['id']] : [$now];

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM blogs b WHERE $baseWhere");
$countStmt->execute($baseParams);
$total = (int)$countStmt->fetchColumn();
$pages = ceil($total / $perPage);

// Add LIMIT / OFFSET via array_merge (safe in all PHP 7.x+)
$queryParams = array_merge($baseParams, [$perPage, $offset]);
$stmt = $pdo->prepare("SELECT b.id, b.title, b.slug, b.excerpt, b.feature_image, b.feature_image_alt,
    b.publish_date, b.read_time, b.views, b.category_ids, a.name as author_name, a.image as author_image
    FROM blogs b LEFT JOIN blog_authors a ON b.author_id = a.id
    WHERE $baseWhere ORDER BY b.publish_date DESC LIMIT ? OFFSET ?");
$stmt->execute($queryParams);
$blogs = $stmt->fetchAll();

// Sidebar categories — use CAST(id AS CHAR) not CAST(id AS JSON)
$categories = $pdo->prepare("
    SELECT bc.name, bc.slug,
        (SELECT COUNT(*) FROM blogs b
         WHERE b.status = 'published'
         AND (b.publish_date IS NULL OR b.publish_date <= ?)
         AND JSON_CONTAINS(b.category_ids, CAST(bc.id AS CHAR))
        ) AS cnt
    FROM blog_categories bc
    WHERE bc.is_active = 1
    HAVING cnt > 0
    ORDER BY cnt DESC
");
$categories->execute([$now]);
$categories = $categories->fetchAll();

$siteUrl = 'https://www.chandigarhuniversity.online';
$pageTitle = $catInfo ? 'Category: '.$catInfo['name'].' | CU Blog' : 'Blog | Chandigarh University Online';
$metaDesc = $catInfo ? 'Browse '.$catInfo['name'].' articles from Chandigarh University Online.'
    : 'Explore expert articles, guides and insights on online education, MBA, BBA, MCA and more from Chandigarh University Online.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
<meta name="robots" content="INDEX, FOLLOW">
<link rel="canonical" href="<?= $siteUrl ?>/blog/<?= $cat ? '?category='.urlencode($cat) : '' ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
<meta property="og:url" content="<?= $siteUrl ?>/blog/">
<meta name="twitter:card" content="summary_large_image">
<link rel="shortcut icon" href="/assets/uploads/favicon.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/bootstrap.min-original.css">
<link rel="stylesheet" href="/assets/css/header-footer.css">
<link rel="stylesheet" href="/assets/css/plugins.css">
<link rel="stylesheet" href="/assets/css/animate.css">
<link rel="stylesheet" href="/assets/css/jquerysctipttop.css">
<link rel="stylesheet" href="/assets/css/custom-style.css">
<link rel="stylesheet" href="/assets/css/responsive-style.css">
<link rel="stylesheet" href="/assets/css/blog-content.css">
<style>
@media(min-width:991px){.cu-logo{height:85px;width:auto}.header-upper .hts-text{display:none}.header-upper .fa-whatsapp{display:none}.header-upper .ph-missed{display:none}}
.aws-logo{width:87px;height:25px;margin-left:3px}
.need-help p{font-size:20px;color:var(--bs-white);font-weight:700;font-family:'GoogleSans';margin-top:-5px;margin-bottom:0;text-decoration:none}
.dropdownHeading{font-size:13px!important}
.main-header-new .navbar .nav-link{font-size:14px;padding-left:8px;padding-right:8px}
@media(min-width:991px){.main-header-new .navbar .nav-link{white-space:nowrap}}
</style>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Blog",
    "name": "Chandigarh University Online Blog",
    "url": "<?= $siteUrl ?>/blog/",
    "description": "<?= htmlspecialchars($metaDesc) ?>"
}
</script>
<style>
body{font-family:'Poppins',sans-serif}
.blog-hero{background:linear-gradient(135deg,#0f1d35 0%,#1a2d4a 60%,#d42b2b 100%);padding:60px 0 50px;position:relative;overflow:hidden}
.blog-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.blog-hero-content{position:relative;z-index:1;text-align:center;color:#fff}
.blog-hero h1{font-size:42px;font-weight:800;margin:0 0 12px;letter-spacing:-0.5px}
.blog-hero p{font-size:17px;color:rgba(255,255,255,0.8);max-width:560px;margin:0 auto 24px}
.blog-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(212,43,43,0.25);border:1px solid rgba(212,43,43,0.4);border-radius:50px;padding:6px 16px;font-size:13px;color:#fca5a5;margin-bottom:16px;backdrop-filter:blur(8px)}

.blog-grid-section{padding:48px 0}
.blog-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
@media(max-width:1024px){.blog-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.blog-grid{grid-template-columns:1fr};.blog-hero h1{font-size:28px}}

.blog-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);transition:transform 0.25s,box-shadow 0.25s;display:flex;flex-direction:column}
.blog-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,0.12)}
.blog-card-img{position:relative;overflow:hidden;aspect-ratio:16/9}
.blog-card-img img{width:100%;height:100%;object-fit:cover;transition:transform 0.4s}
.blog-card:hover .blog-card-img img{transform:scale(1.05)}
.blog-card-cat{position:absolute;top:12px;left:12px;background:#d42b2b;color:#fff;padding:4px 12px;border-radius:50px;font-size:11px;font-weight:700;letter-spacing:0.3px}
.blog-card-body{padding:20px;flex:1;display:flex;flex-direction:column}
.blog-card-title{font-size:16px;font-weight:700;color:#0f1d35;margin:0 0 8px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.blog-card-title a{color:inherit;text-decoration:none}
.blog-card-title a:hover{color:#d42b2b}
.blog-card-excerpt{font-size:13.5px;color:#6b7280;line-height:1.6;margin:0 0 16px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;flex:1}
.blog-card-meta{display:flex;align-items:center;justify-content:space-between;font-size:12px;color:#9ca3af;border-top:1px solid #f1f5f9;padding-top:12px;margin-top:auto}
.blog-card-author{display:flex;align-items:center;gap:6px}
.blog-card-author img{width:22px;height:22px;border-radius:50%;object-fit:cover}
.blog-card-author-initial{width:22px;height:22px;border-radius:50%;background:#0f1d35;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.blog-card-stats{display:flex;gap:10px;align-items:center}

.blog-sidebar{position:sticky;top:20px}
.sidebar-widget{background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.07);margin-bottom:20px}
.sidebar-widget-title{font-size:14px;font-weight:700;color:#0f1d35;margin:0 0 16px;display:flex;align-items:center;gap:8px}
.sidebar-widget-title i{color:#d42b2b}
.cat-list{list-style:none;padding:0;margin:0}
.cat-list li{border-bottom:1px solid #f1f5f9;last-child{border:none}}
.cat-list a{display:flex;align-items:center;justify-content:space-between;padding:8px 0;color:#374151;text-decoration:none;font-size:14px;transition:color 0.2s}
.cat-list a:hover{color:#d42b2b}
.cat-list .count{background:#f1f5f9;color:#9ca3af;padding:2px 8px;border-radius:50px;font-size:11px;font-weight:600}
.cat-list a.active-cat{color:#d42b2b;font-weight:600}

.blog-layout{display:grid;grid-template-columns:1fr 280px;gap:28px;align-items:start}
@media(max-width:900px){.blog-layout{grid-template-columns:1fr}.blog-sidebar{display:none}}

.pagination{display:flex;gap:6px;justify-content:center;flex-wrap:wrap;margin-top:32px}
.page-link{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:14px;font-weight:600;border:1.5px solid #e5e7eb;background:#fff;color:#374151;transition:all 0.2s}
.page-link.active,.page-link:hover{background:#d42b2b;border-color:#d42b2b;color:#fff}

.empty-blog{text-align:center;padding:80px 20px;color:#9ca3af}
.empty-blog i{font-size:48px;color:#e5e7eb;margin-bottom:16px;display:block}

/* Stretch link — makes entire card clickable */
.blog-card{position:relative}
.blog-card-title a::after{content:'';position:absolute;inset:0;z-index:1}
.blog-card-stats a,.blog-card-author a,.cat-list a{position:relative;z-index:2}
/* Fixed header offset */
body{padding-top:165px}
@media(max-width:991px){body{padding-top:70px}}
</style>
</head>
<body>

<!-- Site header -->
<?php include_once __DIR__ . '/../includes/site-header.php'; ?>

<!-- Hero -->
<section class="blog-hero">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px" class="blog-hero-content">
        <div class="blog-hero-badge"><i class="fas fa-graduation-cap"></i> CU Online Blog</div>
        <h1><?= $catInfo ? htmlspecialchars($catInfo['name']) : 'Insights & Guides' ?></h1>
        <p><?= $catInfo ? 'Explore all articles in the <strong>'.htmlspecialchars($catInfo['name']).'</strong> category.' : 'Expert articles on online education, career guidance, MBA, BBA, MCA and more.' ?></p>
    </div>
</section>

<!-- Content -->
<section class="blog-grid-section">
<div style="max-width:1200px;margin:0 auto;padding:0 20px">

    <?php if ($catInfo): ?>
    <div style="margin-bottom:20px">
        <a href="/blog/" style="color:#6b7280;text-decoration:none;font-size:14px"><i class="fas fa-arrow-left"></i> All Categories</a>
    </div>
    <?php endif; ?>

    <div class="blog-layout">
        <!-- Main Grid -->
        <div>
            <?php if ($blogs): ?>
            <div class="blog-grid">
            <?php foreach ($blogs as $b):
                $catIdsArr = json_decode($b['category_ids'] ?? '[]', true);
                $firstCat  = null;
                if ($catIdsArr) {
                    $fc = $pdo->prepare('SELECT name, slug FROM blog_categories WHERE id = ?');
                    $fc->execute([$catIdsArr[0]]);
                    $firstCat = $fc->fetch();
                }
                $pubDate = $b['publish_date'] ? date('d M Y', strtotime($b['publish_date'])) : '';
            ?>
            <article class="blog-card">
                <div class="blog-card-img">
                    <?php if ($b['feature_image']): ?>
                    <img src="<?= htmlspecialchars($b['feature_image']) ?>" 
                         alt="<?= htmlspecialchars($b['feature_image_alt'] ?? $b['title']) ?>"
                         loading="lazy">
                    <?php else: ?>
                    <div style="background:linear-gradient(135deg,#0f1d35,#d42b2b);width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-newspaper" style="font-size:36px;color:rgba(255,255,255,0.5)"></i>
                    </div>
                    <?php endif; ?>
                    <?php if ($firstCat): ?>
                    <span class="blog-card-cat"><?= htmlspecialchars($firstCat['name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="blog-card-body">
                    <h2 class="blog-card-title">
                        <a href="/blog/<?= htmlspecialchars($b['slug']) ?>"><?= htmlspecialchars($b['title']) ?></a>
                    </h2>
                    <p class="blog-card-excerpt"><?= htmlspecialchars($b['excerpt']) ?></p>
                    <div class="blog-card-meta">
                        <div class="blog-card-author">
                            <?php if ($b['author_image']): ?>
                            <img src="<?= htmlspecialchars($b['author_image']) ?>" alt="<?= htmlspecialchars($b['author_name']) ?>">
                            <?php else: ?>
                            <div class="blog-card-author-initial"><?= strtoupper(substr($b['author_name'] ?? 'C', 0, 1)) ?></div>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($b['author_name'] ?? 'CU Team') ?></span>
                        </div>
                        <div class="blog-card-stats">
                            <?php if ($pubDate): ?><span><i class="fas fa-calendar-alt"></i> <?= $pubDate ?></span><?php endif; ?>
                            <?php if ($b['read_time']): ?><span><i class="fas fa-clock"></i> <?= $b['read_time'] ?> min</span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?><?= $cat ? '&category='.urlencode($cat) : '' ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
                <a href="?page=<?= $i ?><?= $cat ? '&category='.urlencode($cat) : '' ?>" class="page-link <?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $pages): ?>
                <a href="?page=<?= $page+1 ?><?= $cat ? '&category='.urlencode($cat) : '' ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-blog">
                <i class="fas fa-newspaper"></i>
                <h3 style="font-size:18px;color:#6b7280;margin-bottom:8px">No articles yet</h3>
                <p>Check back soon for new content!</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="blog-sidebar">
            <div class="sidebar-widget">
                <h3 class="sidebar-widget-title"><i class="fas fa-folder-open"></i> Categories</h3>
                <ul class="cat-list">
                <a href="/blog/" class="<?= !$cat ? 'active-cat' : '' ?>" style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;color:#374151;text-decoration:none;font-size:14px;border-bottom:1px solid #f1f5f9">
                    All Topics <span class="count"><?= $total ?></span>
                </a>
                <?php foreach ($categories as $c): ?>
                <li>
                    <a href="/blog/?category=<?= urlencode($c['slug']) ?>" class="<?= $cat === $c['slug'] ? 'active-cat' : '' ?>">
                        <?= htmlspecialchars($c['name']) ?>
                        <span class="count"><?= (int)$c['cnt'] ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contact CTA -->
            <div class="sidebar-widget" style="background:linear-gradient(135deg,#0f1d35,#1a2d4a);color:#fff">
                <h3 style="color:#fff;font-size:15px;font-weight:700;margin:0 0 10px"><i class="fas fa-phone-alt" style="color:#d42b2b;margin-right:8px"></i>Get Counseling</h3>
                <p style="font-size:13px;color:rgba(255,255,255,0.75);margin:0 0 16px;line-height:1.6">Talk to our admissions counselor about the right program for you.</p>
                <a href="/contact-us.html" style="background:#d42b2b;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:block;text-align:center"><i class="fas fa-arrow-right"></i> Contact Us Now</a>
            </div>
        </aside>
    </div>
</div>
</section>

<!-- Site footer -->
<?php include_once __DIR__ . '/../includes/site-footer.php'; ?>
<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/plugins.js" defer></script>
<script src="/assets/js/lazysizes.min.js" async></script>
<script src="/assets/js/utils.js" async></script>
</body>
</html>
