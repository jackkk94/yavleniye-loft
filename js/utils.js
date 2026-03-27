function parseUtmParameters(url) {
  const urlObj = new URL(url);
  return [...urlObj.searchParams.entries()]
    .filter(([k]) => k.toLowerCase().startsWith('utm'))
    .map(([name, value]) => ({ name, value }));
}