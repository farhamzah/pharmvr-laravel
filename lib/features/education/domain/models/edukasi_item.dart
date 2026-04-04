enum EdukasiType { video, document, module }

class EdukasiItem {
  final String id;
  final String title;
  final String description;
  final String? thumbnailUrl;
  final String contentUrl;
  final EdukasiType type;
  final int durationMinutes;
  final String courseModule;

  const EdukasiItem({
    required this.id,
    required this.title,
    required this.description,
    this.thumbnailUrl,
    required this.contentUrl,
    required this.type,
    required this.durationMinutes,
    required this.courseModule,
  });

  // Future backend serialization
  factory EdukasiItem.fromJson(Map<String, dynamic> json) {
    EdukasiType type;
    switch (json['type']) {
      case 'video':
        type = EdukasiType.video;
        break;
      case 'module':
        type = EdukasiType.module;
        break;
      default:
        type = EdukasiType.document;
    }
    return EdukasiItem(
      id: json['id'] as String,
      title: json['title'] as String,
      description: json['description'] as String,
      thumbnailUrl: json['thumbnail_url'] as String?,
      contentUrl: json['content_url'] as String,
      type: type,
      durationMinutes: json['duration_minutes'] as int? ?? 5,
      courseModule: json['course_module'] as String? ?? 'General CPOB',
    );
  }

  // Mock data for initial frontend build
  static List<EdukasiItem> mockList() {
    return [
      // ── MODULES ──
      const EdukasiItem(
        id: 'MOD-005',
        title: 'Cleanroom Gowning Protocol',
        description: 'Modul pelatihan lengkap: Teori, Pre-Test, Simulasi VR, dan Post-Test pemakaian APD steril di ruang bersih kelas A/B.',
        thumbnailUrl: 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=800&q=80',
        contentUrl: '',
        type: EdukasiType.module,
        durationMinutes: 45,
        courseModule: 'CPOB Lanjutan',
      ),
      const EdukasiItem(
        id: 'MOD-002',
        title: 'Sterile Gloving Technique',
        description: 'Pelajari teknik pemasangan sarung tangan steril sesuai standar WHO dan CPOB untuk area produksi aseptis.',
        thumbnailUrl: 'https://images.unsplash.com/photo-1584036561566-baf8f5f1b144?w=800&q=80',
        contentUrl: '',
        type: EdukasiType.module,
        durationMinutes: 30,
        courseModule: 'Aseptis Dasar',
      ),
      const EdukasiItem(
        id: 'MOD-003',
        title: 'Laminar Airflow Operation',
        description: 'Operasikan Laminar Airflow Hood secara benar. Termasuk kalibrasi, pembersihan, dan uji integritas filter HEPA.',
        thumbnailUrl: 'https://images.unsplash.com/photo-1581093458791-9f3c3900df4b?w=800&q=80',
        contentUrl: '',
        type: EdukasiType.module,
        durationMinutes: 35,
        courseModule: 'Peralatan Lab',
      ),
      // ── VIDEOS ──
      const EdukasiItem(
        id: '1',
        title: 'Pengenalan CPOB Dasar',
        description: 'Video pengenalan konsep dasar Cara Pembuatan Obat yang Baik.',
        thumbnailUrl: 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=800&q=80',
        contentUrl: 'https://example.com/video1.mp4',
        type: EdukasiType.video,
        durationMinutes: 12,
        courseModule: 'Module 1',
      ),
      const EdukasiItem(
        id: '3',
        title: 'Peralatan Lab Kromatografi',
        description: 'Pengenalan alat dan tata cara kalibrasi dasar HPLC.',
        thumbnailUrl: 'https://images.unsplash.com/photo-1581093458791-9f3c3900df4b?w=800&q=80',
        contentUrl: 'https://example.com/video2.mp4',
        type: EdukasiType.video,
        durationMinutes: 24,
        courseModule: 'Module 3',
      ),
      const EdukasiItem(
        id: '4',
        title: 'Panduan Gowning Lengkap',
        description: 'Langkah demontrasi pemakaian APD steril sesuai pedoman WHO.',
        thumbnailUrl: 'https://images.unsplash.com/photo-1584036561566-baf8f5f1b144?w=800&q=80',
        contentUrl: 'https://example.com/video3.mp4',
        type: EdukasiType.video,
        durationMinutes: 18,
        courseModule: 'Module 1',
      ),
      // ── DOCUMENTS ──
      const EdukasiItem(
        id: '2',
        title: 'SOP Pembersihan Ruang Steril',
        description: 'Dokumen standar operasional prosedur untuk area produksi steril.',
        thumbnailUrl: null,
        contentUrl: 'https://example.com/doc1.pdf',
        type: EdukasiType.document,
        durationMinutes: 15,
        courseModule: 'Module 2',
      ),
      const EdukasiItem(
        id: '5',
        title: 'Checklist Validasi Mesin',
        description: 'Formulir checklist untuk validasi kualifikasi Kinerja (PQ).',
        thumbnailUrl: null,
        contentUrl: 'https://example.com/doc2.pdf',
        type: EdukasiType.document,
        durationMinutes: 5,
        courseModule: 'Module 4',
      ),
    ];
  }
}
