using Grappbox.ViewModel;
using Newtonsoft.Json;
using Windows.UI.Xaml.Media.Imaging;

namespace Grappbox.Model
{
    class ProjectSettingsModel : ViewModelBase
    {
        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("name")]
        public string Name { get; set; }

        [JsonProperty("description")]
        public string Description { get; set; }

        [JsonProperty("creator")]
        public UserModel Creator { get; set; }

        [JsonProperty("logo")]
        public string LogoDate { get; set; }

        public string LogoString { get; set; }

        [JsonProperty("phone")]
        public string Phone { get; set; }

        [JsonProperty("company")]
        public string Company { get; set; }

        [JsonProperty("contact_mail")]
        public string ContactMail { get; set; }

        [JsonProperty("facebook")]
        public string Facebook { get; set; }

        [JsonProperty("twitter")]
        public string Twitter { get; set; }

        [JsonProperty("color")]
        public string Color { get; set; }

        [JsonProperty("created_at")]
        public string CreatedAt { get; set; }

        [JsonProperty("deleted_at")]
        public string DeletedAt { get; set; }
        
        public BitmapImage Logo { get; set; }

        //#region FormatedStrings
        //private string logoDateFmt;
        //private string logoImgFmt;
        //#endregion
        //public async System.Threading.Tasks.Task LogoUpdate()
        //{
        //    logoDateFmt = "LogoDate_" + AppGlobalHelper.ProjectId;
        //    logoImgFmt = "LogoImg_" + AppGlobalHelper.ProjectId;
        //    if (LogoDate == null)
        //        return;
        //    DateTime update;
        //    if (DateTimeFormator.DateModelToDateTime(LogoDate, out update) == false)
        //        return;
        //    string tmp = SettingsManager.getOption<string>(logoDateFmt);
        //    DateTime stored = new DateTime();
        //    if (tmp != null && tmp != "")
        //        stored = DateTime.Parse(tmp);
        //    if (DateTime.Compare(stored, update) < 0)
        //    {
        //        SettingsManager.setOption(logoDateFmt, update.ToString());
        //        await getProjectLogo();
        //    }
        //}
        //public async System.Threading.Tasks.Task getProjectLogo()
        //{
        //    LogoModel logoMod = null;
        //    ApiCommunication api = ApiCommunication.Instance;
        //    object[] token = { User.GetUser().Token, AppGlobalHelper.ProjectId };
        //    HttpResponseMessage res = await api.Get(token, "projects/getprojectlogo");
        //    if (res.IsSuccessStatusCode)
        //    {
        //        logoMod = HttpRequestManager.DeserializeJson<LogoModel>(await res.Content.ReadAsStringAsync());
        //        Logo = BytesToImage.String64ToImage(logoMod.Logo);
        //        await BytesToImage.StoreImage(logoMod.Logo, logoImgFmt);
        //    }
        //    else
        //    {
        //        Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
        //    }
        //}

        //public async System.Threading.Tasks.Task SetLogo()
        //{
        //    string tmp = await BytesToImage.GetStoredImage(logoImgFmt);
        //    Logo = tmp == null ? BytesToImage.GetDefaultLogo() : BytesToImage.String64ToImage(tmp);
        //}
    }
}