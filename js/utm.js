function parseUtmParameters(url) {
  const urlObj = new URL(url);
  const params = urlObj.searchParams;
  const utmParams = params.entries().reduce((acc, [k, v]) => {
    if (k.toLowerCase().startsWith('utm')) {
      acc.push({ name: k, value: v });
    }

    return acc;
  }, []);

  return utmParams;
}
