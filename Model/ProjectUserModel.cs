using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class ProjectUserModel
    {
        private int _id;
        private string _firstname;
        private string _lastname;

        [JsonProperty("id")]
        public int Id
        {
            get { return _id; }
            set { _id = value; }
        }

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

        static private ProjectUserModel instance = null;

        static public ProjectUserModel GetInstance()
        {
            return instance;
        }
        public ProjectUserModel()
        {
            instance = this;
        }
    }
}
