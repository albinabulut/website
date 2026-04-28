    </main>
    <footer>
        <p>&copy; <?= date("Y") ?> GK Takı. Tüm hakları saklıdır.</p>
    </footer>

    <!-- Sepete Ekle Onay Modalı (Ekranın Ortasında Çıkan Pencere) -->
    <div id="onayModal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="modalMesaj"></h3>
            <div class="modal-buttons">
                <button id="devamEtBtn" class="btn btn-secondary" style="margin-right: 10px;">Alışverişe Devam Et</button>
                <a href="sepet.php" class="btn">Sepete Git</a>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('onayModal');
    const modalMesaj = document.getElementById('modalMesaj');
    const devamEtBtn = document.getElementById('devamEtBtn');
    
    // Modal'ı (pencereyi) kapatma fonksiyonu
    const closeModal = () => { modal.style.display = 'none'; };
    if (devamEtBtn) devamEtBtn.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal(); // Arka plana tıklayınca kapat
        });
    }

    // Tüm "Sepete Ekle" butonlarını yakala
    document.querySelectorAll('a[href^="sepete_ekle.php"]').forEach(function(buton) {
        buton.addEventListener('click', function(e) {
            e.preventDefault(); // Linkin varsayılan davranışını engelle

            const url = this.href + (this.href.includes('?') ? '&' : '?') + 'ajax=1';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (modalMesaj) modalMesaj.textContent = data.message;
                        if (modal) modal.style.display = 'flex'; // Ekranın tam ortasında göster
                    } else {
                        alert(data.message || 'Bir hata oluştu.');
                    }
                })
                .catch(error => console.error('Hata:', error));
        });
    });
});
</script>
</body>
</html>