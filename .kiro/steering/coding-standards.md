# Kodlama Standartları

- Projelerde hiçbir şekilde yorum satırı (comment) kullanılmaz. Ne PHP, ne Blade, ne JS dosyalarında yorum satırı olmamalıdır.

# Mimari Prensipler

- Tekrarlanan işlemler tek bir merkezi yapıdan yönetilir. Her modül/model için ayrı ayrı aynı işi yapmak yasaktır.
- Yeni bir özellik eklendiğinde otomatik olarak devreye girmesi sağlanır. Manuel ekleme gerektiren yapılar tercih edilmez.
- Örnek: Loglama gibi cross-cutting concern'ler trait yerine Provider'da otomatik model taramasıyla yapılır.
- Tek bir yerden yönet, her yeri etkile. Ayrı ayrı yapıldığında hata ve unutma riski artar.
- Kod hızlı okunabilir ve anlaşılır olmalı. Gereksiz karmaşıklıktan kaçınılır.
- PHP kod yazım stili:
  - Array parametreleri fonksiyon çağrısından sonra yeni satırda başlar: `validate(\n[` şeklinde, `validate([` şeklinde DEĞİL
  - `response()->json(\n[` şeklinde, `response()->json([` şeklinde DEĞİL
  - Key-value hizalaması yapılır: `'key'   => 'value'` (en uzun key'e göre hizala)
  - `try` / `catch` / `if` / `else` süslü parantezleri yeni satırda açılır
  - Mantıksal bloklar arasında boş satır bırakılır
- Bu yazım kuralları sadece PHP değil, TÜM dillerde (TypeScript, JavaScript, SCSS, Blade, vb.) aynı titizlikle uygulanır. Okunabilirlik ve hizalama her yerde geçerlidir.
- Bir işlem yapılırken birden fazla işi çözecek şekilde düşünülür. Tekil çözümler yerine genel çözümler üretilir.
- Mevcut dosyaları düzenlerken KESİNLİKLE fsWrite kullanılmaz. Sadece strReplace ile değişiklik yapılır. Kullanıcının yazdığı kod asla silinmez veya üzerine yazılmaz.
- Migration dosyası oluşturulduğunda KESİNLİKLE otomatik migrate edilmez. Kullanıcıdan izin alınmadan `php artisan migrate` çalıştırılmaz.

# İletişim Dili

- Kullanıcıyla her zaman Türkçe iletişim kurulur. Varsayılan dil Türkçe'dir, ayrıca hatırlatma gerekmez.
