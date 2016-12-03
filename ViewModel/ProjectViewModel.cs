using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.ViewModel
{
    class ProjectViewModel : ViewModelBase
    {
        private int _id;
        private string _name;
        private string _description;
        private string _phone;
        private string _company;
        private string _logoDate;
        private string _email;
        private string _facebook;
        private string _twitter;
        private string _deletedAt;
        private int _finishedTasks;
        private int _ongoingTasks;
        private int _totalTasks;
        private int _bugs;
        private string _messages;

        public int Id
        {
            get { return _id; }
            set
            {
                _id = value;
                NotifyPropertyChanged("Id");
            }
        }
        public string Name
        {
            get { return _name; }
            set
            {
                _name = value;
                NotifyPropertyChanged("Name");
            }
        }
        public string Description
        {
            get { return _description; }
            set
            {
                _description = value;
                NotifyPropertyChanged("Description");
            }
        }
        public string Phone
        {
            get { return _phone; }
            set
            {
                _phone = value;
                NotifyPropertyChanged("Phone");
            }
        }
        public string Company
        {
            get { return _company; }
            set
            {
                _company = value;
                NotifyPropertyChanged("Company");
            }
        }
        public string LogoDate
        {
            get { return _logoDate; }
            set
            {
                _logoDate = value;
                NotifyPropertyChanged("LogoDate");
            }
        }
        public string Email
        {
            get { return _email; }
            set
            {
                _email = value;
                NotifyPropertyChanged("Email");
            }
        }
        public string Facebook
        {
            get { return _facebook; }
            set
            {
                _facebook = value;
                NotifyPropertyChanged("Facebook");
            }
        }
        public string Twitter
        {
            get { return _twitter; }
            set
            {
                _twitter = value;
                NotifyPropertyChanged("Twitter");
            }
        }
        public string DeletedAt
        {
            get { return _deletedAt; }
            set
            {
                _deletedAt = value;
                NotifyPropertyChanged("DeletedAt");
            }
        }
        public int FinishedTasks
        {
            get { return _finishedTasks; }
            set
            {
                _finishedTasks = value;
                NotifyPropertyChanged("FinishedTasks");
            }
        }
        public int OngoingTasks
        {
            get { return _ongoingTasks; }
            set
            {
                _ongoingTasks = value;
                NotifyPropertyChanged("OngoingTasks");
            }
        }
        public int TotalTasks
        {
            get { return _totalTasks; }
            set
            {
                _totalTasks = value;
                NotifyPropertyChanged("TotalTasks");
            }
        }
        public int Bugs
        {
            get { return _bugs; }
            set
            {
                _bugs = value;
                NotifyPropertyChanged("Bugs");
            }
        }
        public string Messages
        {
            get { return _messages; }
            set
            {
                _messages = value;
                NotifyPropertyChanged("Messages");
            }
        }

        public ProjectViewModel UpdateProject(ProjectListModel model)
        {
            Id = model.Id;
            Name = model.Name;
            Email = model.Email;
            Description = model.Description;
            Company = model.Company;
            Phone = model.Phone;
            Facebook = model.Facebook;
            Twitter = model.Twitter;
            Bugs = model.Bugs;
            Messages = model.Messages;
            OngoingTasks = model.OngoingTasks;
            FinishedTasks = model.FinishedTasks;
            TotalTasks = model.TotalTasks;
            DeletedAt = model.DeletedAt;

            return this;
        }

        public ProjectViewModel(ProjectListModel model)
        {
            Id = model.Id;
            Name = model.Name;
            Email = model.Email;
            Description = model.Description;
            Company = model.Company;
            Phone = model.Phone;
            Facebook = model.Facebook;
            Twitter = model.Twitter;
            Bugs = model.Bugs;
            Messages = model.Messages;
            OngoingTasks = model.OngoingTasks;
            FinishedTasks = model.FinishedTasks;
            TotalTasks = model.TotalTasks;
            DeletedAt = model.DeletedAt;
        }
    }
}
