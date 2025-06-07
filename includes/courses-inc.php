<!--  Set any page specific graphics to preload-->

<?php require_once ("../meta/$page-$lang.php");?>

<STYLE>

.course-grid {
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  gap:20px;
}

.course-module-box {
  background: var(--darker);
  border-radius: 12px;
  overflow: hidden;
  display:flex;
  flex-direction: column;
  transition: transform 0.2s, box-shadow 0.2s;
  width:100%;
  max-width:300px;
}

.course-module-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
  cursor:pointer;
}

.course-module-box img {
  width:100%;
  display:block;
}

.course-date-lang-bar {
  background: grey;
  color: white;
  text-align: right;
  font-family: 'Mulish', sans-serif;
  font-size: 1.2em;
  padding: 5px 10px;
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
  background: limegreen;
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
  background: green;
}

@media (min-width:1000px) {
  .course-module-box { flex-basis: calc(33.33% - 20px); }
}
@media (min-width:769px) and (max-width:999px) {
  .course-module-box { flex-basis: calc(50% - 20px); }
}
@media (max-width:768px) {
  .course-module-box { flex-basis: 100%; }
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

<?php require_once ("../header-2024.php");?>
