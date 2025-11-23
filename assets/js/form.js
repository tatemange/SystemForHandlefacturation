

const lastBtn = [...document.querySelectorAll(".group-end button:last-child")]
const allForms = [...document.querySelectorAll(".form")]
lastBtn.forEach((btn) => {
    btn.addEventListener("click", () => {
        allForms.forEach(form => form.classList.toggle("mask"))
    })
})


/**
 * 
 * @param {HTMLElement} input 
 */
function handleInput(input) {
    const length = input.getAttribute("max")

}


