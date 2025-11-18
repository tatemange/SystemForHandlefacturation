

const lastBtn = [...document.querySelectorAll(".group-end button:last-child")]
const allForms = [...document.querySelectorAll(".form")]
lastBtn.forEach((btn) => {
    btn.addEventListener("click", () => {
        allForms.forEach(form => form.classList.toggle("mask"))
    })
})
