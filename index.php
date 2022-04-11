
        <?php include("./header.php") ?>

        <div id="app">

        <div class="section">
            <div class="notification is-danger" v-if="errorMessage">
            <button class="delete"></button>
                {{ errorMessage }}
            </div>
            <div class="notification is-success" v-else="successMessage">
            <button class="delete"></button>
                {{ successMessage }} OK
            </div>

            <button class="button is-primary" @click="showingaddModal = true;">Nuovo articolo</button>

            <!-- list -->
            <div v-if="users">
                <p class="title">Articoli</p>
                <table class="table">
                    <tbody>
                        <tr v-for="user in users" :key="user.id_article">
                            <td>
                                <figure class="image is-128x128" @click="showinguploadimgModal = true; selectUser(user);">
                                    <img v-if="user.img" :src="'./media/'+user.img">
                                    <img v-else :src="'./public/casadelparrucchiereit-logo.jpg'">
                                </figure>
                            </td>
                            <td>{{user.id_article}}</td>
                            <td>{{user.title}}</td>
                            <td>{{ new Date(user.created*1000).toLocaleDateString("it-IT") }}</td>
                            <td>
                                <div class="tags">
                                    <span class="tag is-primary" v-for="tag in user.tags" :key="tag.id_tag">{{tag.tag_name}}</span>
                                </div>
                                <div @click="showingtaggingModal = true; selectUser(user);">edit</div>
                            </td>
                            <td><button class="button is-small" :class="[ user.active==1?'is-primary':'' ]" @click="toggleActive(user.id_article);">{{user.active==1?"attivo":"disattivo"}}</button></td>
                            <td>
                                <div class="buttons">
                                <button class="button is-small" @click="showingeditModal = true; selectUser(user);">Edit</button>
                                <button class="button is-small is-danger" @click="showingdeleteModal = true; selectUser(user);">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div><!-- if -->
        </div><!-- section -->

        <!-- add modal -->
        <div class="modal is-active" id="addmodal" v-if="showingaddModal">
            <div class="modal-background"></div>
            <div class="modal-content">
                <div class="columns is-centered">
                    <div class="column has-text-left has-text-white">
                        <p class="title has-text-white">Aggiungi nuovo articolo</p>
                        <hr />
                        <div>
                            <div class="field">
                                <label class="label has-text-white">Titolo</label>
                                <div class="control">
                                    <input id="title" v-model="newUser.title" class="input" type="text"
                                        placeholder="Text input">
                                </div>
                            </div>

                            <!--div class="field">
                                <label class="label has-text-white">Email</label>
                                <div class="control">
                                    <input id="email" v-model="newUser.email" class="input" type="textarea"
                                        placeholder="Text input">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label has-text-white" style="z-index: 10000">Phone</label>
                                <div class="control">
                                    <input id="phn" v-model="newUser.mobile" class="input" type="text"
                                        placeholder="Text input">
                                </div>
                            </div-->
                        </div>
                        <hr />
                        <button type="button" class="button is-success"
                            @click="showingaddModal = false; addUser();">Save
                            changes</button>
                        <button type="button" class="button is-danger" 
                            @click="showingaddModal = false;">Close</button>
                    </div><!-- column -->
                </div><!-- columns -->
            </div><!-- modal-content -->
        </div> <!-- add modal -->

        <!-- edit modal -->
        <div class="modal is-active" id="editmodal" v-if="showingeditModal">
            <div class="modal-background"></div>
            <div class="modal-content">
                <div class="columns is-centered">
                    <div class="column has-text-left has-text-white">
                        <p class="title has-text-white">Modifica articolo</p>
                        <hr />
                        <div>
                            <div class="field">
                                <label class="label has-text-white">Titolo</label>
                                <div class="control">
                                    <input id="title" v-model="clickedUser.title" class="input" type="text"
                                        placeholder="Text input">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label has-text-white">Testo</label>
                                <div class="control">
                                    <textarea id="content" v-model="clickedUser.content" class="textarea" 
                                    rows="4" cols="50" placeholder="Text input"></textarea>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label has-text-white">Link</label>
                                <div class="control">
                                    <input id="link" v-model="clickedUser.link" class="input" type="text"
                                        placeholder="Text input">
                                </div>
                            </div>

                        </div>
                        <hr />
                        <button type="button" class="button is-success"
                            @click="showingeditModal = false; updateUser();">Save
                            changes</button>
                        <button type="button" class="button is-danger" 
                            @click="showingeditModal = false;">Close</button>
                    </div><!-- column -->
                </div><!-- columns -->
            </div><!-- modal-content -->
        </div> <!-- add modal -->

        <!-- delete modal -->
        <div class="modal is-active" id="deletemodal" v-if="showingdeleteModal">
            <div class="modal-background"></div>
            <div class="modal-content">
				<p class="title has-text-white">Delete user</p>
				<hr/>
				<div class="modal-body">
						<div>
							<p class="has-text-white">Are you sure you want to delete?</p>
							<h3 class="has-text-white">{{clickedUser.title}}</h3>
						</div>
					<hr/>
						<button type="button" class="button is-danger" @click="showingdeleteModal = false; deleteUser();">Yes</button>
						<button type="button" class="button is-warning" @click="showingdeleteModal = false;">No</button>
				</div><!-- modal body -->
			</div><!-- modal content -->
        </div><!-- delete modal -->
        
        <!-- uploadimg modal -->
        <div class="modal is-active" id="uploadimgmodal" v-if="showinguploadimgModal">
            <div class="modal-background"></div>
            <div class="modal-content">
				<p class="title has-text-white">Carica nuova immagine</p>
				<hr/>
				<div class="modal-body">
                <input class="has-text-white" type="file" id="file" ref="file" /><br/>
                <button type="button" class="button is-danger" @click="showinguploadimgModal = false; uploadImage();">Upload</button>
				<button type="button" class="button is-warning" @click="showinguploadimgModal = false;">Cancella</button>
            </div><!-- modal body -->
            </div><!-- modal content -->
        </div><!-- uploadimg modal -->


        <!-- tagging modal -->
        <div class="modal is-active" id="taggingmodal" v-if="showingtaggingModal">
            <div class="modal-background"></div>
            <div class="modal-content">
				<p class="title has-text-white">Seleziona tag</p>
				<hr/>
				<div class="modal-body">
                    
                    <div v-for="(items, name) in tagsGrouped" :key="name">
                        <div class="has-text-white">{{name}}</div>
                        <div class="tags">
                        <span v-for="item in items" class="tag" :key="item.id_tag" :class="[hasTag(item.id_tag)?'is-primary':'is-light']" @click="toggleTag(item.id_tag)">{{item.tag_name}}</span>
                        </div>
                        <hr/>
                    </div>
                    <button type="button" class="button is-warning" @click="showingtaggingModal = false;">Chiudi</button>
                </div><!-- modal body -->
            </div><!-- modal content -->
        </div><!-- tagging modal -->


        <!-- edit modal -->
        <!--div class="modal is-active" id="editmodal" v-if="showingeditModal">
            <div class="modal-background"></div>
            <div class="modal-content">
                <div class="columns is-centered">
                    <div class="column has-text-left has-text-white">
                        <p class="title has-text-white">Add user</p>
                        <hr />
                        
                            <div v-for="field in userToEdit.fields">
                                <div class="field">
                                  {{field.name}}: <span class="data" v-show="!field.edit">{{ field.value }}</span> 
                                  <input type="text" v-model="field.value" v-show="field.edit" v-bind:id="field.id" v-on:blur="saveEdit(field)" :ref="field.id">
                                  <a href="#" v-on:click.prevent="toggleEdit(field, $event)">edit</a>
                                  
                                </div>
                            </div>
                        <hr />
                        <button type="button" class="button is-success"
                            @click="showingeditModal = false; updateUser();">Save
                            changes</button>
                        <button type="button" class="button is-danger" 
                            @click="showingeditModal = false;">Close</button>
                    </div><!- column ->
                </div><!- columns ->
            </div><!- modal-content ->
        </div--> <!-- edit modal -->

        </div><!-- app -->

<script src="app/index.js?3"></script>

<?php include("./footer.php") ?>
