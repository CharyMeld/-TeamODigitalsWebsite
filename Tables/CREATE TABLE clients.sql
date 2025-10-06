
CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `logo`, `description`, `website`) VALUES
(1, 'Faculty of Economics and Management Sciences', 'client1.jpg', 'The Faculty of Economics and Management Sciences was created by the Department of Economics to cater for the demands of stakeholders who apparently are in favour of the departure from the traditional economics education and training in Ibadan, which used to be a highly concentrated one encompassing all aspects of management related areas of economics, business administration, banking and finance, and accountancy as well as marketing. ', 'https://econs.ui.edu.ng/'),
(2, 'West African College of Physicians ', 'client2.jpg', 'Since 1976, WACP have been responsible for postgraduate specialist training of doctors in the five Anglophone West African countries, to increase equity and achievement for all candidates.', 'https://wacpcoam.org/');

