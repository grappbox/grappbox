using GrappBox.ViewModel;
using Newtonsoft.Json;
using System.Collections.ObjectModel;

namespace GrappBox.Model
{
    public class UserModel
    {
        [JsonProperty("id")]
        public int Id {get;set;}
        [JsonProperty("firstname")]
        public string Firstname { get; set; }
        [JsonProperty("lastname")]
        public string Lastname { get; set; }
        [JsonProperty("percent")]
        public int Percent { get; set; }
        public ObservableCollection<ProjectRoleModel> RoleList
        {
            get
            {
                return ProjectSettingsViewModel.GetViewModel().RoleList;
            }
        }
    }
}