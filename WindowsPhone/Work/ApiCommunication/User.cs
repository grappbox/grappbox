using GrappBox.Model;
using GrappBox.Model.Global;
using GrappBox.Ressources;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Xaml.Media.Imaging;
using Windows.Web.Http;

namespace GrappBox.ApiCom
{
    class User
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("firstname")]
        public string Firstname{ get; set; }
        [JsonProperty("lastname")]
        public string Lastname{ get; set; }
        [JsonProperty("email")]
        public string Email{ get; set; }
        [JsonProperty("token")]
        public string Token{ get; set; }
        [JsonProperty("avatar")]
        public DateModel AvatarDate{ get; set; }

        public BitmapImage Avatar { get; set; }
        public string FullName
        {
            get { return Firstname + " " + Lastname; }
        }

        #region FormatedStrings
        private string logoDateFmt;
        private string logoImgFmt;
        #endregion
        public async System.Threading.Tasks.Task LogoUpdate()
        {
            logoDateFmt = "LogoDate_" + Id;
            logoImgFmt = "LogoImg_" + Id;
            if (AvatarDate == null)
                return;
            DateTime update;
            if (DateTimeFormator.DateModelToDateTime(AvatarDate, out update) == false)
                return;
            string tmp = SettingsManager.getOption<string>(logoDateFmt);
            DateTime stored = new DateTime();
            if (tmp != null && tmp != "")
                stored = DateTime.Parse(tmp);
            if (DateTime.Compare(stored, update) < 0)
            {
                SettingsManager.setOption(logoDateFmt, update.ToString());
                await getProjectLogo();
            }
        }
        public async System.Threading.Tasks.Task getProjectLogo()
        {
            LogoModel logoMod = null;
            ApiCommunication api = ApiCommunication.Instance;
            object[] token = { User.GetUser().Token, Id };
            HttpResponseMessage res = await api.Get(token, "user/getuseravatar");
            if (res.IsSuccessStatusCode)
            {
                logoMod = api.DeserializeJson<LogoModel>(await res.Content.ReadAsStringAsync());
                Avatar = BytesToImage.String64ToImage(logoMod.Avatar);
                await BytesToImage.StoreImage(logoMod.Avatar, logoImgFmt);
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        public async System.Threading.Tasks.Task SetLogo()
        {
            string tmp = await BytesToImage.GetStoredImage(logoImgFmt);
            Avatar = tmp == null ? BytesToImage.GetDefaultLogo() : BytesToImage.String64ToImage(tmp);
        }

        static private User instance = null;
        static public User GetUser()
        {
            if (instance != null)
                return instance;
            return new User();
        }
        public User()
        {
            Avatar = null;
            instance = this;
        }
    }
}