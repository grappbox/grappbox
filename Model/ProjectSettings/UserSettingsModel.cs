using Newtonsoft.Json;
using System;
using Windows.UI.Xaml.Media.Imaging;

namespace GrappBox.Model
{
    class UserSettingsModel
    {
        [JsonProperty("firstname")]
        public string Firstname { get; set; }
        [JsonProperty("lastname")]
        public string Lastname { get; set; }
        [JsonProperty("birthday")]
        public string Birthday { get; set; }
        [JsonProperty("email")]
        public string Email { get; set; }
        [JsonProperty("phone")]
        public string Phone { get; set; }
        [JsonProperty("country")]
        public string Country { get; set; }
        [JsonProperty("avatar")]
        public string AvatarDate { get; set; }
        public string av { get; set; }
        [JsonProperty("linkedin")]
        public string Linkedin { get; set; }
        [JsonProperty("viadeo")]
        public string Viadeo { get; set; }
        [JsonProperty("twitter")]
        public string Twitter { get; set; }
        public BitmapImage Avatar { get; set; }

        //#region FormatedStrings
        //private string logoDateFmt;
        //private string logoImgFmt;
        //#endregion
        //public async System.Threading.Tasks.Task LogoUpdate()
        //{
        //    logoDateFmt = "LogoDate_" + User.GetUser().Id;
        //    logoImgFmt = "LogoImg_" + User.GetUser().Id;
        //    if (AvatarDate == null)
        //        return;
        //    DateTime update;
        //    if (DateTimeFormator.DateModelToDateTime(AvatarDate, out update) == false)
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
        //    object[] token = { User.GetUser().Token, User.GetUser().Id };
        //    HttpResponseMessage res = await api.Get(token, "user/getuseravatar");
        //    if (res.IsSuccessStatusCode)
        //    {
        //        logoMod = api.DeserializeJson<LogoModel>(await res.Content.ReadAsStringAsync());
        //        Avatar = BytesToImage.String64ToImage(logoMod.Avatar);
        //        await BytesToImage.StoreImage(logoMod.Avatar, logoImgFmt);
        //    }
        //    else
        //    {
        //        Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
        //    }
        //}

        //public async System.Threading.Tasks.Task SetLogo()
        //{
        //    string tmp = await BytesToImage.GetStoredImage(logoImgFmt);
        //    Avatar = tmp == null ? BytesToImage.GetDefaultLogo() : BytesToImage.String64ToImage(tmp);
        //}

        static private UserSettingsModel instance = null;

        static public UserSettingsModel GetUser()
        {
            return instance;
        }
    }
}
