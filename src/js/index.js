import { initFlowbite } from 'flowbite';
import pdfViewer from "./pdfViewer.js";
import {DataTable} from "simple-datatables"


document.addEventListener('DOMContentLoaded', () => {
  initFlowbite();
  pdfViewer.init();


  const dataTable = new DataTable("#search-table", {
    searchable: true,
    fixedHeight: true,
    labels: {
      placeholder: "Поиск...",       // Поисковая строка
      perPage: "",
      noRows: "Нет данных для отображения",
      info: "",
      loading: "Загрузка...",
      noResults: "Ничего не найдено",
      previous: "Предыдущая",
      next: "Следующая",
    }
  })


  const trs = document.querySelectorAll('#search-table tbody tr');

  if(trs.length > 0){
    trs.forEach(tr => {
      tr.addEventListener('click', (e) => {
        e.preventDefault();
        if (tr.dataset.href !== undefined) {
          document.location.href = tr.dataset.href
        }
      })
    })
  }


});
