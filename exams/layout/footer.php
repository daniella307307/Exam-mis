<?php
// layout/footer.php - Common footer for all pages
?>
</div> <!-- Close main-content -->

<!-- FOOTER -->
<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>📚 Quizly</h4>
                <p>Modern Exam Management System</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/Exam-mis/exams/home.php">Home</a></li>
                    <li><a href="/Exam-mis/exams/join_exam.php">Take Exam</a></li>
                    <li><a href="/Exam-mis/exams/exam_creator_working.php">Create Exam</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Help</h4>
                <ul>
                    <li><a href="#">Contact Support</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Documentation</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-divider"></div>
        <div class="footer-bottom">
            <p>&copy; 2026 Quizly. All rights reserved. | Made with ❤️ for Education</p>
        </div>
    </div>
</footer>

<style>
    /* FOOTER STYLES */
    .main-footer {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: white;
        margin-top: 60px;
        border-top: 3px solid var(--primary);
    }

    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 24px 20px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
        margin-bottom: 30px;
    }

    .footer-section h4 {
        margin-bottom: 16px;
        font-size: 16px;
    }

    .footer-section p {
        font-size: 13px;
        color: #bbb;
        line-height: 1.6;
    }

    .footer-section ul {
        list-style: none;
    }

    .footer-section li {
        margin-bottom: 8px;
    }

    .footer-section a {
        color: #bbb;
        text-decoration: none;
        transition: color 0.3s;
        font-size: 13px;
    }

    .footer-section a:hover {
        color: white;
    }

    .footer-divider {
        height: 1px;
        background: rgba(255,255,255,0.1);
        margin-bottom: 20px;
    }

    .footer-bottom {
        text-align: center;
        font-size: 12px;
        color: #999;
    }

    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .footer-container {
            padding: 30px 16px 15px;
        }
    }
</style>

<!-- Mobile menu toggle script -->
<script>
    document.getElementById('menuToggle')?.addEventListener('click', function() {
        document.getElementById('navLinks').classList.toggle('active');
    });

    // Close menu when link is clicked
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            document.getElementById('navLinks').classList.remove('active');
        });
    });

    // Function to set breadcrumb (use in child pages)
    function setBreadcrumb(items) {
        const breadcrumb = document.getElementById('breadcrumb');
        if (!breadcrumb) return;

        let html = '<a href="/Exam-mis/exams/home.php">🏠 Home</a>';
        items.forEach((item, index) => {
            html += ' <span>/</span> ';
            if (index === items.length - 1) {
                html += `<span>${item.name}</span>`;
            } else {
                html += `<a href="${item.url}">${item.name}</a>`;
            }
        });
        breadcrumb.innerHTML = html;
    }
</script>

</body>
</html>
