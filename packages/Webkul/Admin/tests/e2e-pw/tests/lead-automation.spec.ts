import { test, expect } from "../setup";

const adminCredentials = {
    email: "admin@example.com",
    password: "admin123",
};

async function createHotLead(adminPage) {
    await adminPage.goto("admin/leads");
    await adminPage.getByRole('link', { name: 'Create Lead' }).click();

    const leadTitle = `Hot Lead ${Date.now()}`;
    const leadDescription = 'urgent buy now need today';

    await adminPage.fill('input[name="title"]', leadTitle);
    await adminPage.fill('textarea[name="description"]', leadDescription);
    await adminPage.locator('select[name="lead_source_id"]').selectOption("1");
    await adminPage.locator('select[name="lead_type_id"]').selectOption("1");
    await adminPage.locator('select[name="user_id"]').selectOption("1");
    await adminPage.fill('input[name="lead_value"]', '1000');

    const contactSection = adminPage.locator('#contact-person');
    await contactSection.getByText('Click to Add', { exact: true }).first().click();
    const personSearch = contactSection.locator('.absolute input[type="text"]').first();
    await personSearch.waitFor({ state: 'visible' });
    await personSearch.fill('Hot Buyer');
    await personSearch.dispatchEvent('change');
    await adminPage.waitForTimeout(600);
    await contactSection.getByText('Add as New').first().click();

    await adminPage.fill('input[name="person[emails][0][value]"]', `hot-${Date.now()}@example.com`);
    await adminPage.fill('input[name="person[contact_numbers][0][value]"]', '1234567890');

    while (await adminPage.locator('#products .icon-delete').count() > 0) {
        await adminPage.locator('#products .icon-delete').first().click();
    }

    await adminPage.getByRole('button', { name: 'Save' }).click();
    await adminPage.waitForURL(/\/admin\/leads(?:\?.*)?$/);

    return leadTitle;
}

test.describe('lead automation', () => {
    test('should show an automation result card on a hot lead', async ({ adminPage }) => {
        const leadTitle = await createHotLead(adminPage);

        await adminPage.goto('admin/leads');
        const searchInput = adminPage.getByRole('textbox', { name: 'Search by Title' });
        await searchInput.fill(leadTitle);
        await searchInput.press('Enter');

        const leadLink = adminPage
            .locator('a[href*="/admin/leads/view/"]')
            .filter({ hasText: leadTitle })
            .first();

        await expect(leadLink).toBeVisible();
        await leadLink.click();
        await adminPage.waitForTimeout(2000);

        await expect(adminPage.getByText(/Automation Result/i)).toBeVisible();
        await expect(adminPage.getByText(/HOT/i)).toBeVisible();
    });
});
