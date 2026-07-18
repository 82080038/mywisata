# MVC Modular Reorganization - Future Task

> **Status:** PENDING  
> **Priority:** LOW  
> **Reason:** Large-scale refactoring, not required for modern features to function  
> **Estimated Effort:** 2-3 weeks

---

## Overview

MVC Modular Reorganization adalah refactoring besar untuk memindahkan seluruh codebase ke struktur modular yang lebih terorganisir. Ini akan memindahkan 35+ controllers, 23+ models, dan views ke folder module masing-masing.

**Catatan Penting:** Refactoring ini TIDAK diperlukan untuk modern features (Modules 40-45) berfungsi. Modern features sudah fully implemented dan siap untuk production.

---

## Current Structure

```
app/
├── controllers/      # 35+ controllers (flat structure)
├── models/           # 23+ models (flat structure)
├── services/         # 10+ services (flat structure)
└── views/            # Organized by controller name
    ├── auth/
    ├── admin/
    ├── bookings/
    └── ...
```

## Target Structure

```
app/
├── modules/
│   ├── Auth/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Views/
│   ├── Booking/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Views/
│   ├── Admin/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Views/
│   └── ... (10 modules total)
├── core/             # Base classes (App, Controller, Model, View, Repository, Service)
└── shared/           # Shared components (Helpers, Middleware)
```

---

## Implementation Phases

### Phase 1: Base Classes ✅ COMPLETED
- Create `Repository.php` base class
- Create `Service.php` base class
- Update existing base classes if needed

### Phase 2: Module Structure ✅ COMPLETED
- Create 10 module folders
- Set up basic structure for each module

### Phase 3: Migrate Controllers (35 files) ⏳ PENDING
- Move each controller to its module folder
- Update namespace
- Update autoloader
- Test each controller

### Phase 4: Migrate Models & Create Repositories (23 models) ⏳ PENDING
- Move each model to its module folder
- Create repository for each model
- Update model to use repository pattern
- Update namespace
- Test each model

### Phase 5: Create Services (all modules) ⏳ PENDING
- Create service classes for each module
- Move business logic from controllers to services
- Update controllers to use services
- Test each service

### Phase 6: Migrate Views ⏳ PENDING
- Move views to module folders
- Update view paths in controllers
- Test each view

### Phase 7: Update Routing ⏳ PENDING
- Update `App.php` to handle new namespaces
- Update URL patterns
- Test all routes

### Phase 8: Update Autoloader ⏳ PENDING
- Update `composer.json` with new namespace structure
- Run `composer dump-autoload`
- Test autoloading

### Phase 9: Testing ⏳ PENDING
- Run unit tests
- Run integration tests
- Run end-to-end tests
- Fix any issues

### Phase 10: Documentation ⏳ PENDING
- Update README with new structure
- Update developer documentation
- Create migration guide

---

## Modules to Create

1. **Auth** - Authentication & authorization
2. **Admin** - Admin panel & management
3. **Booking** - Booking management
4. **Destination** - Destination management
5. **TourGuide** - Tour guide management
6. **Hotel** - Hotel management
7. **Restaurant** - Restaurant management
8. **Event** - Event management
9. **User** - User management
10. **ModernFeatures** - Modern features (Modules 40-45)

---

## Risks & Considerations

### Risks
- **Breaking Changes:** Large refactoring may break existing functionality
- **Time-Consuming:** Estimated 2-3 weeks for full implementation
- **Testing Overhead:** Extensive testing required after each phase
- **Deployment Complexity:** Need to deploy entire refactored codebase at once

### Mitigation
- **Feature Flags:** Use feature flags to enable/disable new structure
- **Incremental Deployment:** Deploy module by module if possible
- **Comprehensive Testing:** Test thoroughly before each commit
- **Rollback Plan:** Keep backup of working version

---

## Recommendation

**Untuk saat ini, disarankan untuk MENUNDA refactoring ini karena:**

1. Modern features sudah fully implemented dan berfungsi
2. Refactoring ini tidak menambah fitur baru
3. Risiko breaking changes tinggi
4. Membutuhkan waktu 2-3 minggu
5. Bisa dilakukan sebagai project terpisah di kemudian hari

**Fokus saat ini:**
- Testing modern features
- Bug fixes untuk modern features
- User feedback dan iteration
- Production deployment modern features

---

## When to Implement

Refactoring ini sebaiknya dilakukan ketika:
- Modern features sudah stabil di production
- Ada waktu dedicated untuk refactoring (2-3 minggu)
- Team siap untuk testing ekstensif
- Ada roadmap untuk fitur-fitur baru yang membutuhkan struktur modular

---

## References

- Original plan: `prompting/01_development/46_MVC_MODULAR_REORGANIZATION.md`
- Current state: `prompting/state.json` (mvc_modular_reorganization section)
