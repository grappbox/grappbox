#include <QException>
#include <QDebug>
#include "SliderMenu.h"

SliderMenu::SliderMenu(QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QVBoxLayout();
    this->setLayout(_MainLayout);
    _MainLayout->setMargin(0);
    _MainLayout->setSpacing(0);
    _CurrentIndex = 0;
}

int SliderMenu::AddMenuItem(QString name)
{
    QPushButton *newItem = new QPushButton(name);
    newItem->setMaximumHeight(40);
    newItem->setStyleSheet("QPushButton {"
                           "background-color: #d9d9d9;"
                           "border-style: none;"
                           "border-bottom-style: solid;"
                           "border-width: 2px;"
                           "border-color: #595959;"
                           "}");
    newItem->setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);
    _ListButton.push_back(newItem);
    _MainLayout->addWidget(newItem);
    QObject::connect(newItem, SIGNAL(clicked(bool)), this, SLOT(ButtonChangeMenu()));
    return _ListButton.size() - 1;
}

QString SliderMenu::GetMenuItem(int id)
{
    if (id < 0 || id > _ListButton.size())
        throw new QException();
    return _ListButton[id]->text();
}

void SliderMenu::ForceChangeMenu(int menu)
{
    if (menu < 0 || menu > _ListButton.size())
        throw new QException();
    _CurrentIndex = menu;
    for (QList<QPushButton*>::iterator it = _ListButton.begin(); it != _ListButton.end(); ++it)
    {
        (*it)->setStyleSheet("QPushButton {"
                               "background-color: #d9d9d9;"
                               "border-style: none;"
                               "border-bottom-style: solid;"
                               "border-width: 2px;"
                               "border-color: #595959;"
                               "}");
    }
    _ListButton[menu]->setStyleSheet("QPushButton {"
                                     "background-color: #c9c9c9;"
                                     "border-style: none;"
                                     "border-bottom-style: solid;"
                                     "border-width: 2px;"
                                     "border-color: #c0392b;"
                                     "}");
}

void SliderMenu::ButtonChangeMenu()
{
    QObject *obj = QObject::sender();
    int index = _ListButton.indexOf(dynamic_cast<QPushButton*>(obj));
    ForceChangeMenu(index);
    emit MenuChanged(index);
}
