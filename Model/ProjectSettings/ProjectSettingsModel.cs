using Grappbox.Resources;
using Grappbox.ViewModel;
using Newtonsoft.Json;
using System;
using System.Diagnostics;
using Windows.UI.Xaml.Media.Imaging;
using Windows.Web.Http;

namespace GrappBox.Model
{
    class ProjectSettingsModel : ViewModelBase
    {
        private int _id;
        private string _name;
        private string _description;
        private string _phone;
        private string _company;
        private string _contactMail;
        private string _facebook;
        private string _twitter;
        private string _color;
        private string _creationDate;
        private string _deletedAt;

        [JsonProperty("id")]
        public int Id
        {
            get { return _id; }
            set { _id = value; }
        }

        [JsonProperty("name")]
        public string Name
        {
            get { return _name; }
            set { _name = value; }
        }

        [JsonProperty("description")]
        public string Description
        {
            get { return _description; }
            set { _description = value; }
        }

        [JsonProperty("logo")]
        public string LogoDate { get; set; }

        public string LogoString { get; set; }

        [JsonProperty("phone")]
        public string Phone
        {
            get { return _phone; }
            set { _phone = value; }
        }

        [JsonProperty("company")]
        public string Company
        {
            get { return _company; }
            set { _company = value; }
        }

        [JsonProperty("contact_mail")]
        public string ContactMail
        {
            get { return _contactMail; }
            set { _contactMail = value; }
        }

        [JsonProperty("facebook")]
        public string Facebook
        {
            get { return _facebook; }
            set { _facebook = value; }
        }

        [JsonProperty("twitter")]
        public string Twitter
        {
            get { return _twitter; }
            set { _twitter = value; }
        }

        [JsonProperty("color")]
        public string Color
        {
            get { return _color; }
            set { _color = value; }
        }

        [JsonProperty("creation_date")]
        public string CreationDate
        {
            get { return _creationDate; }
            set { _creationDate = value; }
        }

        [JsonProperty("deleted_at")]
        public string DeletedAt
        {
            get { return _deletedAt; }
            set { _deletedAt = value; }
        }
        
        public BitmapImage Logo { get; set; }

        //#region FormatedStrings
        //private string logoDateFmt;
        //private string logoImgFmt;
        //#endregion
        //public async System.Threading.Tasks.Task LogoUpdate()
        //{
        //    logoDateFmt = "LogoDate_" + SettingsManager.getOption<int>("ProjectIdChoosen");
        //    logoImgFmt = "LogoImg_" + SettingsManager.getOption<int>("ProjectIdChoosen");
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
        //    object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
        //    HttpResponseMessage res = await api.Get(token, "projects/getprojectlogo");
        //    if (res.IsSuccessStatusCode)
        //    {
        //        logoMod = api.DeserializeJson<LogoModel>(await res.Content.ReadAsStringAsync());
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
        public ProjectSettingsModel()
        {
            _name = "";
            _description = "";
            _phone = "";
            _company = "";
            _contactMail = "";
            _facebook = "";
            _twitter = "";
        }
    }
}