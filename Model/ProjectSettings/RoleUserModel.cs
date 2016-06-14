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
        private int _id;
        private string _name;
        private List<ProjectUserModel> _userAssigned;
        private List<ProjectUserModel> _userNonAssigned;

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

        [JsonProperty("users_assigned")]
        public List<ProjectUserModel> UsersAssigned
        {
            get { return _userAssigned; }
            set { _userAssigned = value; }
        }

        [JsonProperty("users_non_assigned")]
        public List<ProjectUserModel> UsersNonAssigned
        {
            get { return _userNonAssigned; }
            set { _userNonAssigned = value; }
        }

        static private RoleUserModel instance = null;

        static public RoleUserModel GetInstance()
        {
            return instance;
        }
        public RoleUserModel()
        {
            instance = this;
        }
    }
}
