﻿using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class CustomerAccessModel
    {
        private string _name;
        private string _token;
        private int _id;
        private DateModel _creationDate;

        [JsonProperty("name")]
        public string Name
        {
            get { return _name; }
            set { _name = value; }
        }

        [JsonProperty("customer_token")]
        public string Token
        {
            get { return _token; }
            set { _token = value; }
        }

        [JsonProperty("id")]
        public int Id
        {
            get { return _id; }
            set { _id = value; }
        }

        [JsonProperty("creation_date")]
        public DateModel CreationDate
        {
            get { return _creationDate; }
            set { _creationDate = value; }
        }

        static private CustomerAccessModel instance = null;

        static public CustomerAccessModel GetInstance()
        {
            return instance;
        }
        public CustomerAccessModel()
        {
            instance = this;
        }
    }
}
