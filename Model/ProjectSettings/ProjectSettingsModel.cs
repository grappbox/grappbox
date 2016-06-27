using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class ProjectSettingsModel
    {
        private string _name;
        private string _description;
        private string _logo;
        private string _phone;
        private string _company;
        private string _contactMail;
        private string _facebook;
        private string _twitter;
        private string _color;
        private DateModel _creationDate;
        private DateModel _deletedAt;

        [JsonProperty("name")]
        public string Name
        {
            get { return _name; }
            set { _name = value; }
        }

        [JsonProperty("description")]
        public string Description
        {
            get { return _description; }
            set { _description = value; }
        }

        [JsonProperty("logo")]
        public string Logo
        {
            get { return _logo; }
            set { _logo = value; }
        }

        [JsonProperty("phone")]
        public string Phone
        {
            get { return _phone; }
            set { _phone = value; }
        }

        [JsonProperty("company")]
        public string Company
        {
            get { return _company; }
            set { _company = value; }
        }

        [JsonProperty("contact_mail")]
        public string ContactMail
        {
            get { return _contactMail; }
            set { _contactMail = value; }
        }

        [JsonProperty("facebook")]
        public string Facebook
        {
            get { return _facebook; }
            set { _facebook = value; }
        }

        [JsonProperty("twitter")]
        public string Twitter
        {
            get { return _twitter; }
            set { _twitter = value; }
        }

        [JsonProperty("color")]
        public string Color
        {
            get { return _color; }
            set { _color = value; }
        }

        [JsonProperty("creation_date")]
        public DateModel CreationDate
        {
            get { return _creationDate; }
            set { _creationDate = value; }
        }

        [JsonProperty("deleted_at")]
        public DateModel DeletedAt
        {
            get { return _deletedAt; }
            set { _deletedAt = value; }
        }

        static private ProjectSettingsModel instance = null;

        static public ProjectSettingsModel GetInstance()
        {
            return instance;
        }
        public ProjectSettingsModel()
        {
            instance = this;
            _name = "";
            _description = "";
            _logo = "";
            _phone = "";
            _company = "";
            _contactMail = "";
            _facebook = "";
            _twitter = "";
        }
    }
}