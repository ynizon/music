@extends('layouts.app')

@section('content')
@php
$user = Auth::user();
@endphp
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
					<div class="row">
						<div class="col-md-8 col_1 mypad">
							<br/>
							<h1 >FAQ</h1>
							<div>
								<ul class="faq">
									<li>
										<h2>Qu'est ce que site ?</h2>
										<p>Ce site est un mashup de différents sites (LastFM, Youtube, FlickR, Wikipédia)
										</p>
									</li>

									<li>
										<h2>J'ai des problèmes d'accès</h2>
										<p>Ce site a été fait pour mon usage personnel. Si il venait à tomber du fait de trop nombreuses connexions, je le passerais sur un github ou vous pourrez vous faire une copie de ce site,
										mais cette adresse ne sera plus utilisable.
										</p>
									</li>

									<li>
										<h2>Comment télécharger un morceau ?</h2>
										<p>En théorie il est possible de télécharger les sons des vidéos Youtube (<a target="_blank" href='https://rg3.github.io/youtube-dl/'>youtube-dl</a>), mais
										comme Youtube n'est pas d'accord, et bien je ne propose pas ce service.
										</p>
									</li>

									<li>
										<h2>A quoi sert-il d'avoir un compte et d'y relier son compte LastFM ?</h2>
										<p>Cela permet de rassembler vos artistes préférés en page d'accueil.
										</p>
									</li>

									<li>
										<h2>Protection des données ?</h2>
										<p>Votre login LastFM est uniquement stocké dans votre navigateur (cookie), donc même en cas de piratage, vous serez protégé.
										</p>
									</li>

									<li>
										<h2>Comment gagnes tu de l'argent avec ce site ?</h2>
										<p>Je n'en gagne pas. Mais si le service venait à être très utilisé, je serais surement obligé de prendre un meilleure hébergement et le service deviendra
										alors payant (mais pas cher). Il y a aussi quelques liens sponsos d'amazon (mais qui ne sont jamais cliqués).
										</p>
									</li>

									<li>
										<h2>Pourquoi certaines vidéos ne se lancent pas ?</h2>
										<p>Elles sont protégées par les ayants droits, mais je n'ai aucun moyen de le savoir avant lancement.
										</p>
									</li>

									<li>
										<h2>Puis je faire la promotion de ce site ?</h2>
										<p>Les boutons sociaux en bas de page sont là pour ça ;-)
										</p>
									</li>
								  </ul>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
