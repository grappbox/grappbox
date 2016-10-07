using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class RoleUserModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("name")]
        public string Name { get; set; }
        [JsonProperty("users_assigned")]
        public List<UserModel> UsersAssigned { get; set; }
        [JsonProperty("users_non_assigned")]
        public List<UserModel> UsersNonAssigned { get; set; }
    }
}
