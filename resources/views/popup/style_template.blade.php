<style>



    .toc-wrapper {
        transition: left 0.3s ease-in-out;
        overflow-y: auto;
        overflow-x: hidden;
        position: fixed;
        z-index: 30;
        top: 0;
        left: 0;
        bottom: 0;
        width: 18%;
        background-color: #F3F7F9;
        font-size: 13px;
        font-weight: bold
    }

    .toc-wrapper .lang-selector {
        display: none
    }

    .toc-wrapper .lang-selector a {
        padding-top: 0.5em;
        padding-bottom: 0.5em
    }

    .toc-wrapper .logo {
        display: block;
        max-width: 100%;
        margin-bottom: 0px
    }

    .toc-wrapper>.search {
        position: relative
    }

    .toc-wrapper>.search input {
        background: #F3F7F9;
        border-width: 0 0 1px 0;
        border-color: #666;
        padding: 6px 0 6px 20px;
        box-sizing: border-box;
        margin: 10px 15px;
        width: 250px;
        outline: none;
        color: #5C6975;
        border-radius: 0
    }

    .toc-wrapper>.search:before {
        position: absolute;
        top: 17px;
        left: 15px;
        color: #5C6975
    }

    .toc-wrapper .search-results {
        margin-top: 0;
        box-sizing: border-box;
        height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        transition-property: height, margin;
        transition-duration: 180ms;
        transition-timing-function: ease-in-out;
        background: #F3F7F9
    }

    .toc-wrapper .search-results.visible {
        height: 30%;
        margin-bottom: 1em
    }

    .toc-wrapper .search-results li {
        margin: 1em 15px;
        line-height: 1
    }

    .toc-wrapper .search-results a {
        color: #5C6975;
        text-decoration: none
    }

    .toc-wrapper .search-results a:hover {
        text-decoration: underline
    }

    .toc-wrapper ul,.toc-wrapper li {
        list-style: none;
        margin: 0;
        padding: 0;
        line-height: 28px
    }

    .toc-wrapper li {
        color: #5C6975;
        transition-property: background;
        transition-timing-function: linear;
        transition-duration: 200ms
    }

    .toc-wrapper .toc-list-h1-title {
        background-color: #F3F7F9;
        text-align: center
    }

    .toc-wrapper .toc-link.active {
        background-color: #ffffff;
        color: #2855af
    }

    .toc-wrapper .toc-link.active-parent {
        background-color: #F3F7F9;
        color: #5C6975
    }

    .toc-wrapper .toc-list-h2 {
        display: none;
        background-color: #F3F7F9;
        font-weight: 500
    }

    .toc-wrapper .toc-h2 {
        padding-left: 25px;
        font-size: 14px;
        font-weight: bold
    }

    .toc-wrapper .toc-list-h3 {
        display: none;
        background-color: #F3F7F9
    }

    .toc-wrapper .toc-h3 {
        padding-left: 40px;
        font-size: 12px
    }

    .toc-wrapper .toc-list-h4 {
        display: none;
        background-color: #F3F7F9
    }

    .toc-wrapper .toc-h4 {
        padding-left: 55px;
        font-size: 12px
    }

    .toc-wrapper .toc-footer {
        padding: 1em 0;
        margin-top: 1em;
        border-top: 1px dashed #666
    }

    .toc-wrapper .toc-footer li,.toc-wrapper .toc-footer a {
        color: #5C6975;
        text-decoration: none
    }

    .toc-wrapper .toc-footer a:hover {
        text-decoration: underline
    }

    .toc-wrapper .toc-footer li {
        font-size: 0.8em;
        line-height: 1.7;
        text-decoration: none
    }

    .toc-link,.toc-footer li {
        padding: 0 15px 0 15px;
        display: block;
        overflow-x: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        text-decoration: none;
        color: #5C6975;
        transition-property: background;
        transition-timing-function: linear;
        transition-duration: 130ms
    }

    .page-wrapper {
        margin-left: 19%;
        position: relative;
        z-index: 10;
        background-color: #FFFFFF;
        min-height: 100%;
        padding-bottom: 1px
    }

    .page-wrapper .dark-box {
        background-color: #102B60;
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0
    }

</style>
