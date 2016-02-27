using GrappBox.ViewModel;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class ProjectListModel : ViewModelBase
    {
        private int _id;
        private string _name;

        [JsonProperty("project_id")]
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

        static private ProjectListModel instance = null;

        static public ProjectListModel GetUser()
        {
            return instance;
        }
        public ProjectListModel()
        {
            instance = this;
            _id = 0;
            _name = "";
        }
    }
}
