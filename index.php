<?php
require 'header.php';
?>

<style>
/* ===== Hero Section ===== */
.hero {
    position: relative;
    width: 100%;
    height: 100vh; /* full màn hình */
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;

    /* Background ảnh */
    background-image: url('images/hero-bg.png');
    background-repeat: no-repeat;
    background-position: center center;
    background-size: contain; /* hiển thị toàn bộ ảnh */
    background-color:   hsla(139, 100%, 72%, 1.00); /* màu nền đồng bộ với ảnh để lấp khoảng trống */
    overflow: hidden;
}

.hero::after {
    content: "";
    position: absolute;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.5); /* overlay tối để chữ nổi bật */
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2; /* nằm trên overlay */
    max-width: 800px;
    padding: 0 20px;
    animation: fadeInUp 1.2s ease forwards;
    opacity: 0;
}

.hero h1 {
    font-size: 3.5rem;
    margin-bottom: 20px;
    line-height: 1.2;
}

.hero p {
    font-size: 1.4rem;
    margin-bottom: 30px;
}

/* Animations */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(50px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media(max-width: 992px) {
    .hero h1 { font-size: 2.8rem; }
    .hero p { font-size: 1.2rem; }
}


.btn-cta {
    display: inline-block;
    background-color: #1cc88a;
    color: white;
    padding: 14px 30px;
    border-radius: 50px;
    font-size: 1.2rem;
    text-decoration: none;
    transition: 0.3s;
}

.btn-cta:hover {
    background-color: #17a673;
}

/* ===== Features Section ===== */
.features {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    padding: 80px 20px;
    background: #f9f9f9;
}

.feature {
    flex: 1 1 250px;
    background: white;
    padding: 30px 20px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    transition: transform 0.5s, box-shadow 0.5s;
    opacity: 0;
    transform: translateY(50px);

    display: flex;           
    flex-direction: column;  
    align-items: center;     
    justify-content: flex-start;
}

.feature.show {
    opacity: 1;
    transform: translateY(0);
}

.feature img {
    width: 80px;
    height: 80px;           
    object-fit: contain;
    margin-bottom: 20px;
}

.feature h3 {
    font-size: 1.6rem;
    margin-bottom: 12px;    
    line-height: 1.3;
}

.feature p {
    font-size: 1rem;
    color: #555;
    line-height: 1.5;
}
.feature:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}
/* ===== CTA Section ===== */
.cta-section {
    text-align: center;
    padding: 70px 20px;
    background: #1cc88a;
    color: white;
}

.cta-section h2 {
    font-size: 2.5rem;
    margin-bottom: 25px;
}

.cta-section .btn-cta {
    font-size: 1.3rem;
    padding: 14px 40px;
}

/* ===== Animations ===== */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(50px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== Responsive ===== */
@media(max-width: 992px) {
    .hero h1 { font-size: 2.8rem; }
    .hero p { font-size: 1.2rem; }
}

@media(max-width:768px){
    .features { flex-direction: column; align-items: center; }
    .feature { width: 80%; }
}

</style>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Quản lý chi tiêu thông minh</h1>
        <p>Kiểm soát tài chính, tiết kiệm chi tiêu và theo dõi mọi khoản của bạn chỉ trong một nơi.</p>
        <a href="register.php" class="btn-cta">Bắt đầu ngay</a>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="feature">
        <img src="images/icon-budget.png" alt="Ngân sách">
        <h3>Quản lý ngân sách</h3>
        <p>Đặt ngân sách hàng tháng và kiểm soát chi tiêu để không vượt kế hoạch.</p>
    </div>
    <div class="feature">
        <img src="images/icon-chart.png" alt="Biểu đồ">
        <h3>Biểu đồ trực quan</h3>
        <p>Hiển thị chi tiêu theo danh mục giúp bạn nhận biết thói quen tài chính dễ dàng.</p>
    </div>
    <div class="feature">
        <img src="images/icon-alert.png" alt="Cảnh báo">
        <h3>Cảnh báo thông minh</h3>
        <p>Nhận thông báo khi chi tiêu gần đạt hoặc vượt ngân sách.</p>
    </div>
    <div class="feature">
        <img src="images/icon-mobile.png" alt="Truy cập dễ dàng">
        <h3>Dễ dàng truy cập</h3>
        <p>Quản lý chi tiêu mọi lúc mọi nơi trên mọi thiết bị.</p>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <h2>Bắt đầu quản lý tài chính thông minh ngay hôm nay!</h2>
    <a href="register.php" class="btn-cta">Tạo tài khoản miễn phí</a>
</section>

<script>
// Animation khi scroll
function revealFeatures() {
    const features = document.querySelectorAll('.feature');
    const windowHeight = window.innerHeight;
    features.forEach(feature => {
        const top = feature.getBoundingClientRect().top;
        if (top < windowHeight - 100) {
            feature.classList.add('show');
        }
    });
}
window.addEventListener('scroll', revealFeatures);
window.addEventListener('load', revealFeatures);
</script>

<?php
require 'footer.php';
?>

