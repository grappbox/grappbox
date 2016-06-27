using GrappBox.Ressources;
using GrappBox.ViewModel;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class UserSettingsModel
    {
        private string _firstname;
        private string _lastname;
        private DateTime? _birthday;
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
        public DateTime? Birthday
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
        public string Avatar
        {
            get { return _avatar; }
            set { _avatar = value; }
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
            _birthday = new DateTime();
        }
    }
}
