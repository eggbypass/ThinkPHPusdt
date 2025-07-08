if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    // 手机端
    document.getElementById("mobileContent").style.display = "block";
} else {
    // PC端
    document.getElementById("pcContent").style.display = "block";
}

// 获取所有的 <li> 元素
var liElements = document.querySelectorAll("#goods li");

// 添加点击事件监听器
// for (var i = 0; i < liElements.length; i++) {
//     liElements[i].addEventListener("click", function() {
//         // 移除其他 <li> 元素的选中样式
//         for (var j = 0; j < liElements.length; j++) {
//             liElements[j].classList.remove("selected");
//             let tmpInput = liElements[j].querySelector("input[type=radio]")
//             tmpInput.checked = false
//         }
//
//         // 添加选中样式到当前点击的 <li> 元素
//         this.classList.add("selected");
//
//         // 获取当前 <li> 元素中的 <input> 元素
//         var radioInput = this.querySelector("input[type=radio]");
//         // 设置当前 <input> 元素为选中状态
//         radioInput.checked = true;
//     });
// }