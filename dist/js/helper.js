function currencyFormatter(price) {
    return price.toLocaleString("en-US", { style: "currency" , currency:"USD"})
}

function timestamp2Date(timestamp){
    return new Date(timestamp * 1000).toLocaleDateString()
}