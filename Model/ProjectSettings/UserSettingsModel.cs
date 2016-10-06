using Newtonsoft.Json;
using System;
using Windows.UI.Xaml.Media.Imaging;

namespace GrappBox.Model
{
    class UserSettingsModel
    {
        private string _firstname;
        private string _lastname;
        private string _birthday;
        private string _email;
        private string _phone;
        private string _country;
        private string _avatar;
        private string _linkedin;
        private string _viadeo;
        private string _twitter;

        [JsonProperty("firstname")]
        public string Firstname
        {
            get { return _firstname; }
            set { _firstname = value; }
        }

        [JsonProperty("lastname")]
        public string Lastname
        {
            get { return _lastname; }
            set { _lastname = value; }
        }

        [JsonProperty("birthday")]
        public string Birthday
        {
            get { return _birthday; }
            set { _birthday = value; }
        }

        [JsonProperty("email")]
        public string Email
        {
            get { return _email; }
            set { _email = value; }
        }

        [JsonProperty("phone")]
        public string Phone
        {
            get { return _phone; }
            set { _phone = value; }
        }

        [JsonProperty("country")]
        public string Country
        {
            get { return _country; }
            set { _country = value; }
        }

        [JsonProperty("avatar")]
        public string AvatarDate { get; set; }
        public string av
        {
            get
            {
                return _avatar;
            }
            set
            {
                _avatar = value;
            }
        }

        [JsonProperty("linkedin")]
        public string Linkedin
        {
            get { return _linkedin; }
            set { _linkedin = value; }
        }

        [JsonProperty("viadeo")]
        public string Viadeo
        {
            get { return _viadeo; }
            set { _viadeo = value; }
        }

        [JsonProperty("twitter")]
        public string Twitter
        {
            get { return _twitter; }
            set { _twitter = value; }
        }

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
        public UserSettingsModel()
        {
            instance = this;
            _firstname = "";
            _lastname = "";
            _email = "";
            _phone = "";
            _country = "";
            _avatar = "";
            _linkedin = "";
            _viadeo = "";
            _twitter = "";
            _birthday = "";
        }
    }
}
