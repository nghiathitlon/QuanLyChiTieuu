<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'header.php'; 
?>

<style>
/* ===== HERO ===== */
.hero {
    position: relative;
    width: 100%;
    height: 90vh;
    background: url('images/hero-bg.jpg') center/cover no-repeat fixed;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: white;
    text-align: center;
    padding: 0 20px;
}

.hero h1 {
    font-size: 3rem;
    margin-bottom: 20px;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.6);
}

.hero p {
    font-size: 1.5rem;
    margin-bottom: 30px;
    text-shadow: 1px 1px 6px rgba(0,0,0,0.6);
}

.btn-cta {
    padding: 12px 28px;
    background: linear-gradient(90deg, #4e73df, #1cc88a);
    color: white;
    font-weight: 600;
    border-radius: 50px;
    text-decoration: none;
    font-size: 1.2rem;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.btn-cta:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.25);
}

/* ===== FEATURES ===== */
.features {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    padding: 60px 20px;
    background: #f4f4f4;
}

.feature-card {
    background: white;
    width: 260px;
    padding: 30px 20px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
}

.feature-card img {
    width: 60px;
    margin-bottom: 15px;
}

/* ===== COUNTERS ===== */
.counters {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 40px;
    padding: 60px 20px;
}

.counter {
    background: #1cc88a;
    color: white;
    border-radius: 15px;
    width: 180px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.counter h3 {
    font-size: 2rem;
    margin-bottom: 10px;
}

.counter p {
    font-size: 1.1rem;
}

.counter:hover {
    transform: translateY(-5px);
}

/* ===== TESTIMONIALS ===== */
.testimonials {
    background: #fdfdfd;
    padding: 60px 20px;
    text-align: center;
}

.testimonial-card {
    max-width: 500px;
    margin: 20px auto;
    background: #1cc88a;
    color: white;
    padding: 30px 20px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    font-style: italic;
}

/* ===== FOOTER ===== */
footer {
    background: #4e73df;
    color: white;
    padding: 40px 20px;
    text-align: center;
}

footer a {
    color: #ffe600;
    text-decoration: none;
    margin: 0 10px;
}

footer a:hover {
    text-decoration: underline;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .features, .counters {
        flex-direction: column;
        align-items: center;
    }
    .feature-card, .counter {
        width: 80%;
    }
}
</style>

<!-- HERO SECTION -->
<section class="hero">
    <h1>Quản lý chi tiêu thông minh</h1>
    <p>Kiểm soát tài chính dễ dàng, tiết kiệm hiệu quả, nâng cao chất lượng cuộc sống!</p>
    <a href="dashboard.php" class="btn-cta">Bắt đầu ngay</a>
</section>

<!-- FEATURES -->
<section class="features">
    <div class="feature-card">
        <img src="images/icon1.svg" alt="Theo dõi chi tiêu">
        <h3>Theo dõi chi tiêu</h3>
        <p>Biết chính xác bạn đã chi bao nhiêu trong tháng với biểu đồ trực quan.</p>
    </div>
    <div class="feature-card">
        <img src="images/icon2.svg" alt="Ngân sách thông minh">
        <h3>Ngân sách thông minh</h3>
        <p>Đặt và quản lý ngân sách hàng tháng để tránh chi tiêu quá tay.</p>
    </div>
    <div class="feature-card">
        <img src="images/icon3.svg" alt="Cảnh báo & nhắc nhở">
        <h3>Cảnh báo & nhắc nhở</h3>
        <p>Nhận thông báo khi gần vượt ngân sách hoặc thu chi bất thường.</p>
    </div>
</section>

<!-- COUNTERS -->
<section class="counters">
    <div class="counter">
        <h3 class="counter-value" data-target="12000000">0</h3>
        <p>Tổng Thu nhập</p>
    </div>
    <div class="counter">
        <h3 class="counter-value" data-target="8000000">0</h3>
        <p>Tổng Chi tiêu</p>
    </div>
    <div class="counter">
        <h3 class="counter-value" data-target="4000000">0</h3>
        <p>Số dư hiện tại</p>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="testimonials">
    <h2>Người dùng nói gì</h2>
    <div class="testimonial-card">
        "Web giúp tôi kiểm soát chi tiêu, tiết kiệm 30% mỗi tháng và không còn lo lắng về tài chính!"
        <br><strong>- Nguyễn Văn A</strong>
    </div>
    <div class="testimonial-card">
        "Các biểu đồ trực quan và nhắc nhở thông minh thật sự hữu ích, tôi thấy chi tiêu hợp lý hơn."
        <br><strong>- Trần Thị B</strong>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <p>&copy; 2025 Quản lý Chi tiêu. All rights reserved.</p>
    <p>
        <a href="#">Facebook</a> | 
        <a href="#">Instagram</a> | 
        <a href="#">Twitter</a>
    </p>
</footer>

<script>
// COUNTER ANIMATION
const counters = document.querySelectorAll('.counter-value');
counters.forEach(counter => {
    counter.innerText = '0';
    const updateCount = () => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText.replace(/\D/g,'');
        const increment = target / 200;
        if(count < target){
            counter.innerText = Math.ceil(count + increment).toLocaleString();
            setTimeout(updateCount, 10);
        } else {
            counter.innerText = target.toLocaleString();
        }
    };
    updateCount();
});
</script>

<?php require 'footer.php'; ?>
