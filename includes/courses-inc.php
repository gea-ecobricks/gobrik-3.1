<!--  Set any page specific graphics to preload-->

<?php require_once ("../meta/$page-$lang.php");?>

<STYLE>

.course-grid {
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap:20px;
  justify-items:stretch;
}

.course-module-box {
  background: var(--course-module);
  border-radius: 12px;
  overflow: hidden;
  display:flex;
  flex-direction: column;
  transition: transform 0.2s, box-shadow 0.2s;
  width:100%;
  text-decoration:none;
  color:inherit;
}

.course-module-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
  cursor:pointer;
  border: 1px solid var(--emblem-green);
  background: var(--course-module-over);
}

.course-module-box img {
  width:100%;
  display:block;
}

.course-date-lang-bar {
  background: var(--strong-back);
  color: white;
  text-align: right;
  font-family: 'Mulish', sans-serif;
  font-size: 1em;
  padding: 5px 10px;
  transition: background-color 0.2s;
}

.course-module-box:hover .course-date-lang-bar {
  background:var(--emblem-green);
}

.course-module-info {
  padding:15px;
  text-align:left;
}

.course-module-info h3 {
  font-family: 'Arvo', serif;
  font-size:1.5em;
  color: var(--h1);
  margin:0 0 5px 0;
}

.course-module-info h4 {
  font-family: 'Mulish', sans-serif;
  font-size:1.3em;
  color: var(--text-color);
  margin:0 0 8px 0;
}

.training-leaders {
  font-family:'Mulish', sans-serif;
  font-size:1.2em;
  font-weight:400;
  font-style:italic;
  color: var(--text-color);
  margin-bottom:8px;
}

.course-description {
  font-family:'Mulish', sans-serif;
  font-size:1em;
  color: var(--text-color);
  margin-bottom:8px;
}

.module-caption-item {
  font-family:'Mulish', sans-serif;
  font-size:0.9em;
  color: var(--subdued-text);
}

.learn-more-btn {
  background: var(--emblem-green-over);
  color:white;
  text-align:center;
  padding:8px 12px;
  margin:10px;
  border-radius:6px;
  text-decoration:none;
  display:block;
  transition: background-color 0.2s;
}

.course-module-box:hover .learn-more-btn {
  background:var(--emblem-green);
}

#main {
  height:fit-content;
}

#splash-bar {
  background-color: var(--top-header);
  filter:none!important;
  margin-bottom:-200px!important;
}

.form-container {
  max-width:800px!important;
  box-shadow:#0000001f 0px 5px 20px;
}

</style>

<?php require_once ("../header-2025.php");?>
