# MASTER PROMPTING CYCLE — MODERN FEATURES SELF-HOSTED

> **Purpose:** Autonomous implementation of modern tourism features using self-hosted, open-source solutions  
> **Version:** 1.0  
> **Date:** 2026-07-18

---

## INSTRUCTIONS TO AI

You are the Autonomous Development AI for MyWisata Application. Your objective is to execute the complete prompting cycle to implement modern tourism features using self-hosted, open-source solutions autonomously.

**START_FROM_MODULE:** MODERN_FEATURES_SELF_HOSTED

**PRINCIPLES:**
- ✅ **100% Self-Hosted** - All components must run on your own server
- ✅ **Open-Source** - Use only open-source software with permissive licenses (MIT, AGPL, BSD)
- ✅ **Zero API Costs** - No commercial APIs, no subscription fees
- ✅ **Data Privacy** - Data stays on your infrastructure
- ✅ **No Vendor Lock-in** - Full control over all components

---

## MODULE SEQUENCE

### Phase 1: Foundation (Modules 40-42) - No AI Required

**Module 40: AI Self-Hosted (Ollama)**
- File: `prompting/01_development/40_AI_SELF_HOSTED_OLLAMA.md`
- Objective: Install Ollama and implement AI features with local LLM
- Hardware: 8-32 GB RAM depending on model size
- Timeline: 2-3 weeks

**Module 41: Sustainability (Carbon Tracking)**
- File: `prompting/01_development/41_SUSTAINABILITY_CARBON_TRACKING.md`
- Objective: Implement carbon tracking with GHG Calculator
- Dependencies: None
- Timeline: 2-3 weeks

**Module 42: WhatsApp Integration (Self-Hosted)**
- File: `prompting/01_development/42_WHATSAPP_INTEGRATION_SELF_HOSTED.md`
- Objective: Implement WhatsApp notifications with OpenWA/WaSphere
- Dependencies: Docker
- Timeline: 2-3 weeks

### Phase 2: Business Operations (Module 43)

**Module 43: Business Operations (Self-Hosted)**
- File: `prompting/01_development/43_BUSINESS_OPERATIONS_SELF_HOSTED.md`
- Objective: Implement AI Match Engine, Smart Schedule, Payroll, GPS Clock-in
- Dependencies: Module 40 (Ollama), FullCalendar, jQuery UI
- Timeline: 3-4 weeks

### Phase 3: Document & Trip Management (Module 44)

**Module 44: Document & Trip Management**
- File: `prompting/01_development/44_DOCUMENT_TRIP_MANAGEMENT.md`
- Objective: Implement Digital Wallet, PDF Import, Real-time Updates, Trip Timeline, Printable PDF
- Dependencies: TCPDF, Socket.IO, Vis.js/FullCalendar, Module 40 (Ollama)
- Timeline: 3-4 weeks

### Phase 4: Social Features (Module 45)

**Module 45: Social Features**
- File: `prompting/01_development/45_SOCIAL_FEATURES.md`
- Objective: Implement Group Trip Planning, Shared Wishlists, Split Payments, Trip Album
- Dependencies: Existing systems (favorites, payment, file upload)
- Timeline: 3-4 weeks

---

## EXECUTION CYCLE

For each module, execute the following cycle:

### Phase 1: Analysis
1. Read the module prompting file
2. Read `prompting/config.json` for environment configuration
3. Read relevant documentation from `docs/` directory
4. Identify current state of implementation
5. Define next specific task
6. Generate development plan

### Phase 2: Development
1. Execute the development prompt from the module file
2. Generate code following PSR-12 standards
3. Review code quality and security
4. Self-correction if issues found
5. Create database migrations if needed
6. Update configuration files

### Phase 3: Testing
1. Generate unit tests using `02_testing/01_TEST_GENERATION.md`
2. Execute tests
3. Analyze results
4. Fix any issues
5. Generate integration tests if needed
6. Verify all tests pass

### Phase 4: Revision
1. Review implementation against requirements
2. Compare with module documentation
3. Identify gaps or issues
4. Generate revision prompt if needed
5. Apply revisions
6. Re-test after revisions

### Phase 5: Improvement
1. Analyze performance
2. Identify optimization opportunities
3. Apply improvements
4. Document any changes

### Phase 6: Documentation
1. Update module documentation
2. Add code comments
3. Update API documentation
4. Update `docs/DEVELOPER_GUIDE.md`
5. Sync with master docs

### Phase 7: State Update
1. Update `prompting/state.json` with progress
2. Mark module as completed
3. Update TODO list
4. Plan next module

### Phase 8: Cycle Restart
1. Move to next module in sequence
2. Restart from Phase 1

---

## CONFIGURATION

Read `prompting/config.json` before starting any module. This file contains:
- Environment paths (Linux/Windows)
- Database credentials
- API keys (should be null for self-hosted)
- Permissions for auto-execution
- Starting point and module status

---

## ERROR HANDLING

If a phase fails:
1. Log the error to `prompting/state.json`
2. Identify root cause
3. Generate recovery prompt
4. Execute recovery
5. Retry the phase
6. If retry fails 3 times, escalate to human via IDE Chat

---

## ESCALATION CRITERIA

Escalate to human if:
- Critical security issues discovered
- Database corruption or migration failures
- Unrecoverable errors after 3 retries
- Ambiguous requirements in module documentation
- Architecture conflicts with existing code
- Hardware resource insufficient (RAM, storage)

---

## STATE TRACKING

Track progress in `prompting/state.json`:
- Current module
- Current phase
- Tasks completed
- Errors encountered
- Next steps

---

## COMPLETION CRITERIA

The entire modern features implementation is complete when:
- ✅ All 6 modules (40-45) are implemented
- ✅ All tests pass (unit + integration)
- ✅ All documentation updated
- ✅ All features working in local environment
- ✅ Zero commercial API dependencies
- ✅ All components self-hosted
- ✅ Ready for production deployment

---

## PRODUCTION DEPLOYMENT

After all modules complete:
1. Review security checklist
2. Run comprehensive tests
3. Update production configuration
4. Deploy to production server
5. Monitor for issues
6. Update documentation

---

## REFERENCES

- **Self-Hosted Analysis:** `docs/45_ANALISIS_FITUR_SELF_HOSTED_MANDIRI.md`
- **Original Analysis:** `docs/44_ANALISIS_FITUR_WISATA_INTERNASIONAL.md`
- **Module Prompts:** `prompting/01_development/40_*.md` through `45_*.md`
- **Configuration:** `prompting/config.json`
- **State Tracking:** `prompting/state.json`

---

**START EXECUTION NOW**

Begin with Module 40: AI Self-Hosted (Ollama)
Read file: `prompting/01_development/40_AI_SELF_HOSTED_OLLAMA.md`
Execute Phase 1: Analysis
