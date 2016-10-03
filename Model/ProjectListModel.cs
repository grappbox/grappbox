using Grappbox.HttpRequest;
using Grappbox.Model;
using Grappbox.ViewModel;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Popups;
using Windows.UI.Xaml.Media.Imaging;
using Windows.Web.Http;

namespace Grappbox.Model
{
    public class ProjectListModel : ViewModelBase
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("name")]
        public string Name { get; set; }
        [JsonProperty("description")]
        public string Description { get; set; }
        [JsonProperty("phone")]
        public string Phone { get; set; }
        [JsonProperty("company")]
        public string Company { get; set; }
        [JsonProperty("logo")]
        public string LogoDate { get; set; }
        [JsonProperty("contact_mail")]
        public string Email { get; set; }
        [JsonProperty("facebook")]
        public string Facebook { get; set; }
        [JsonProperty("twitter")]
        public string Twitter { get; set; }
        [JsonProperty("deleted_at")]
        public string DeletedAt { get; set; }
        [JsonProperty("number_finished_tasks")]
        public int FinishedTasks { get; set; }
        [JsonProperty("number_ongoing_tasks")]
        public int OngoingTasks { get; set; }
        [JsonProperty("number_tasks")]
        public int TotalTasks { get; set; }
        [JsonProperty("number_bugs")]
        public int Bugs { get; set; }
        [JsonProperty("number_messages")]
        public string Messages { get; set; }
        private BitmapImage _logo;
        public BitmapImage Logo
        {
            get { return _logo; }
            set
            {
                _logo = value;
                NotifyPropertyChanged("Logo");
            }
        }
        #region FormatedStrings
        private string logoDateFmt;
        private string logoImgFmt;
        #endregion
        public async Task LogoUpdate()
        {
            //logoDateFmt = "LogoDate_" + Id.ToString();
            //logoImgFmt = "LogoImg_" + Id.ToString();
            //if (LogoDate == null)
            //    return;
            //DateTime update;
            //if (DateTimeFormator.DateModelToDateTime(LogoDate, out update) == false)
            //    return;
            //string tmp = SettingsManager.getOption<string>(logoDateFmt);
            //DateTime stored = new DateTime();
            //if (tmp != null && tmp != "")
            //    stored = DateTime.Parse(tmp);
            //if (DateTime.Compare(stored, update) < 0)
            //{
            //    SettingsManager.setOption(logoDateFmt, update.ToString());
            //    await getProjectLogo();
            //}
        }
        public async Task<bool> getProjectLogo()
        {
            //LogoModel logoMod = null;
            //HttpRequestManager api = HttpRequestManager.Instance;
            //object[] token = { User.GetUser().Token, Id };
            //HttpResponseMessage res = await api.Get(token, "projects/getprojectlogo");
            //if (res == null)
            //    return false;
            //string json = await res.Content.ReadAsStringAsync();
            //if (res.IsSuccessStatusCode)
            //{
            //    logoMod = api.DeserializeJson<LogoModel>(json);
            //    await BytesToImage.StoreImage(logoMod.Logo, logoImgFmt);
            //}
            //else
            //{
            //    Debug.WriteLine(api.GetErrorMessage(json));
            //    return false;
            //}
            return false;
        }

        public async Task SetLogo()
        {
            string tmp = await BytesToImage.GetStoredImage(logoImgFmt);
            Logo = tmp == null ? BytesToImage.GetDefaultLogo() : BytesToImage.String64ToImage(tmp);
        }
    }
}
