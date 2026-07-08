# Git Workflow Strategy

**Version:** 1.0.0
**Last Updated:** 2026-07-01

---

## Branching Strategy

This project uses a Git branching strategy based on Gitflow, adapted for a smaller team.

### Main Branches

- **`main`** - Production-ready code. Always deployable.
- **`develop`** - Integration branch for features. Next release candidate.

### Supporting Branches

- **`feature/*`** - Feature branches (e.g., `feature/pwa-offline-mode`)
- **`hotfix/*`** - Hotfix branches for production issues (e.g., `hotfix/security-patch`)
- **`release/*`** - Release preparation branches (e.g., `release/v1.0.0`)

---

## Branch Naming Conventions

### Feature Branches
- Format: `feature/[feature-name]`
- Examples:
  - `feature/pwa-offline-mode`
  - `feature/payment-gateway`
  - `feature/ai-personalization`

### Hotfix Branches
- Format: `hotfix/[issue-description]`
- Examples:
  - `hotfix/security-vulnerability`
  - `hotfix/database-connection`
  - `hotfix/payment-failure`

### Release Branches
- Format: `release/[version]`
- Examples:
  - `release/v1.0.0`
  - `release/v1.1.0`
  - `release/v2.0.0`

---

## Workflow

### Feature Development

1. Create feature branch from `develop`:
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/feature-name
   ```

2. Make changes and commit:
   ```bash
   git add .
   git commit -m "feat: add PWA offline mode capability"
   ```

3. Push and create pull request:
   ```bash
   git push origin feature/feature-name
   ```

4. Create PR from `feature/feature-name` to `develop`
5. Get code review and merge

### Hotfix Production

1. Create hotfix branch from `main`:
   ```bash
   git checkout main
   git pull origin main
   git checkout -b hotfix/issue-description
   ```

2. Make fixes and commit:
   ```bash
   git add .
   git commit -m "fix: resolve security vulnerability in login"
   ```

3. Push and create PR:
   ```bash
   git push origin hotfix/issue-description
   ```

4. Create PR from `hotfix/issue-description` to both `main` and `develop`
5. Merge to `main` first, then back-merge to `develop`

### Release Process

1. Create release branch from `develop`:
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b release/v1.0.0
   ```

2. Update version numbers, changelog
3. Finalize release preparation

4. Merge release branch to `main`:
   ```bash
   git checkout main
   git merge --no-ff release/v1.0.0
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin main --tags
   ```

5. Back-merge to `develop`:
   ```bash
   git checkout develop
   git merge --no-ff release/v1.0.0
   git push origin develop
   ```

---

## Commit Message Convention

Follow conventional commits format:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation changes
- `style` - Code style changes (formatting, no logic change)
- `refactor` - Code refactoring
- `perf` - Performance improvement
- `test` - Adding or updating tests
- `chore` - Maintenance tasks
- `ci` - CI/CD changes

### Examples

```
feat(booking): add multi-service shopping cart

Implement shopping cart for booking multiple services (guide, ticket, hotel) in single transaction.

- Add Cart model with add/remove/clear methods
- Create CartController with API endpoints
- Implement cart UI with floating cart icon
- Add cart persistence with localStorage sync

Closes #123
```

```
fix(auth): resolve CSRF token validation issue

CSRF token validation was failing for AJAX requests due to token regeneration.
Fixed by storing token in session and validating against stored value.

Fixes #456
```

---

## Pull Request Guidelines

### PR Title

- Use the same format as commit messages
- Example: `feat(pwa): add service worker for offline support`

### PR Description

- Describe what changes and why
- List related issues
- Include screenshots for UI changes
- Add testing instructions

### PR Checklist

- [ ] Code follows PSR-12 standards
- [ ] Tests added/updated (if applicable)
- [ ] Documentation updated
- [ ] No merge conflicts
- [ ] All tests passing
- [ ] Code review approved

---

## Best Practices

1. **Never commit directly to `main`**
2. **Keep branches small and focused**
3. **Write descriptive commit messages**
4. **Pull frequently to avoid conflicts**
5. **Run tests before pushing**
6. **Delete merged feature branches**
7. **Use issue references in commits**

---

## Git Commands Reference

### Initialize Repository
```bash
git init
git add .
git commit -m "Initial commit"
```

### Setup Remote
```bash
git remote add origin <repository-url>
git branch -M main
git push -u origin main
```

### Create Branch
```bash
git checkout -b feature/feature-name
```

### Switch Branch
```bash
git checkout branch-name
```

### Merge Branch
```bash
git checkout target-branch
git merge source-branch
```

### Delete Branch
```bash
# Local
git branch -d branch-name

# Remote
git push origin --delete branch-name
```

### View History
```bash
git log --oneline --graph --all
```

### Stash Changes
```bash
git stash
git stash pop
```

---

## Repository Status

**Current Status:** Git repository initialized
**Main Branch:** main
**Develop Branch:** develop
**Active Features:** None

---

## Next Steps

1. Ensure `.gitignore` is properly configured
2. Initialize Git repository if not already done
3. Create `develop` branch
4. Set up remote repository (GitHub/GitLab)
5. Configure branch protection rules
6. Set up CI/CD pipeline (FASE 0.8)
