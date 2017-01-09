using Grappbox.ViewModel;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model
{
    public class UsersModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("firstname")]
        public string Firstname { get; set; }
        [JsonProperty("lastname")]
        public string Lastname { get; set; }
        public ObservableCollection<ProjectRoleModel> RoleList
        {
            get
            {
                return ProjectSettingsViewModel.GetViewModel().RoleList;
            }
        }
    }
}
