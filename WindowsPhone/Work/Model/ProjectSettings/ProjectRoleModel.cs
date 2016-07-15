using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class ProjectRoleModel
    {
        private int _id;
        private string _name;
        private int _teamTimeline;
        private int _customerTimeline;
        private int _gantt;
        private int _whiteboard;
        private int _bugtracker;
        private int _event;
        private int _task;
        private int _projectSettings;
        private int _cloud;

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

        [JsonProperty("team_timeline")]
        public int TeamTimeline
        {
            get { return _teamTimeline; }
            set { _teamTimeline = value; }
        }

        [JsonProperty("customer_timeline")]
        public int CustomerTimeline
        {
            get { return _customerTimeline; }
            set { _customerTimeline = value; }
        }

        [JsonProperty("gantt")]
        public int Gantt
        {
            get { return _gantt; }
            set { _gantt = value; }
        }

        [JsonProperty("whiteboard")]
        public int Whiteboard
        {
            get { return _whiteboard; }
            set { _whiteboard = value; }
        }

        [JsonProperty("bugtracker")]
        public int Bugtracker
        {
            get { return _bugtracker; }
            set { _bugtracker = value; }
        }

        [JsonProperty("event")]
        public int Event
        {
            get { return _event; }
            set { _event = value; }
        }

        [JsonProperty("task")]
        public int Task
        {
            get { return _task; }
            set { _task = value; }
        }

        [JsonProperty("project_settings")]
        public int ProjectSettings
        {
            get { return _projectSettings; }
            set { _projectSettings = value; }
        }

        [JsonProperty("cloud")]
        public int Cloud
        {
            get { return _cloud; }
            set { _cloud = value; }
        }
    }
}
