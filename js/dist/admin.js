(()=>{var e={n:s=>{var r=s&&s.__esModule?()=>s.default:()=>s;return e.d(r,{a:r}),r},d:(s,r)=>{for(var t in r)e.o(r,t)&&!e.o(s,t)&&Object.defineProperty(s,t,{enumerable:!0,get:r[t]})},o:(e,s)=>Object.prototype.hasOwnProperty.call(e,s)};(()=>{"use strict";const s=flarum.core.compat["admin/app"];var r=e.n(s);r().initializers.add("fof-username-request",(function(){r().extensionData.for("fof-username-request").registerSetting({setting:"fof-username-request.username_cost",type:"number",label:r().translator.trans("fof-username-request.admin.settings.username_modals.cost")}).registerSetting({setting:"fof-username-request.nickname_cost",type:"number",label:r().translator.trans("fof-username-request.admin.settings.nickname_modals.cost")}).registerPermission({icon:"fa fa-user-edit",label:r().translator.trans("fof-username-request.admin.permissions.moderate_requests"),permission:"user.viewUsernameRequests"},"moderate").registerPermission({icon:"fa fa-user-edit",label:r().translator.trans("fof-username-request.admin.permissions.request_username"),permission:"user.requestUsername"},"start").registerPermission({icon:"fa fa-user-edit",label:r().translator.trans("fof-username-request.admin.permissions.request_nickname"),permission:"user.requestNickname"},"start").registerSetting((function(){return[m("h3",null,"Important"),m("p",null,"In order for permissions to be honored correctly when using ",m("code",null,"flarum/nicknames"),", be sure to set ",m("code",null,"Edit Own Nickname")," to"," ",m("code",null,"admin")," in that extension, and use the permissions provided by this extension instead.")]}))}))})(),module.exports={}})();
//# sourceMappingURL=admin.js.map