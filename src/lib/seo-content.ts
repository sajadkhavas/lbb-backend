export function generateSupportiveSeoHtml(title: string, keywords: string): string {
  const keywordList = keywords.split(',').map(k => k.trim()).filter(Boolean);
  const selectedKeywords = keywordList.slice(0, 5);
  
  if (selectedKeywords.length === 0) return '';

  return `
<h3>کلمات کلیدی مرتبط با ${title}</h3>
<p>برای دسترسی سریع‌تر به اطلاعات تخصصی ${title}، می‌توانید عبارات زیر را جستجو کنید: ${selectedKeywords.map(k => `<strong>${k}</strong>`).join('، ')}.</p>
  `;
}
